<?php

class StadtratsantragParser extends RISParser
{
    private BrowserBasedDowloader $browserBasedDowloader;
    private CurlBasedDownloader $curlBasedDownloader;

    public function __construct(?BrowserBasedDowloader $browserBasedDowloader = null, ?CurlBasedDownloader $curlBasedDownloader = null)
    {
        $this->browserBasedDowloader = $browserBasedDowloader ?: new BrowserBasedDowloader();
        $this->curlBasedDownloader = $curlBasedDownloader ?: new CurlBasedDownloader();
    }

    public function parse(int $id): ?Antrag
    {
        if (SITE_CALL_MODE != "cron") echo "- Antrag $id\n";

        $html = $this->curlBasedDownloader->loadUrl(RIS_BASE_URL . 'antrag/detail/' . $id);

        $parsed = StadtratsantragData::parseFromHtml($html);
        if ($parsed === null) {
            return null;
        }

        $daten = new Antrag();
        $daten->id = $id;
        $daten->datum_letzte_aenderung = new CDbExpression('NOW()');
        $daten->typ = Antrag::$TYP_STADTRAT_ANTRAG;
        $daten->referent = "";
        $daten->kurzinfo = "";
        $daten->initiatorInnen = implode(', ', $parsed->initiativeNamen);
        $daten->gestellt_von = implode(', ', $parsed->gestelltVon);
        $daten->betreff = $parsed->title;
        $daten->antrags_nr = $parsed->antragsnummer;
        $daten->status = $parsed->status;
        $daten->bearbeitung = $parsed->bearbeitungsart ?: '';
        $daten->antrag_typ = $parsed->typ;
        $daten->referat = $parsed->referatName;
        $daten->referat_id = $parsed->referatId;
        $daten->gestellt_am = $parsed->gestelltAm?->format('Y-m-d');
        $daten->wahlperiode = $parsed->wahlperiode;
        $daten->bearbeitungsfrist = $parsed->bearbeitungsfrist?->format('Y-m-d');
        $daten->erledigt_am = $parsed->erledigtAm?->format('Y-m-d');

        $aenderungen = "";

        /** @var Antrag $alter_eintrag */
        $alter_eintrag = Antrag::model()->findByPk($id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($alter_eintrag->bearbeitungsfrist != $daten->bearbeitungsfrist) $aenderungen .= "Bearbeitungsfrist: " . $alter_eintrag->bearbeitungsfrist . " => " . $daten->bearbeitungsfrist . "\n";
            if ($alter_eintrag->status != $daten->status) $aenderungen .= "Status: " . $alter_eintrag->status . " => " . $daten->status . "\n";
            if ($alter_eintrag->fristverlaengerung != $daten->fristverlaengerung) $aenderungen .= "Fristverlängerung: " . $alter_eintrag->fristverlaengerung . " => " . $daten->fristverlaengerung . "\n";
            if ($alter_eintrag->initiatorInnen != $daten->initiatorInnen) $aenderungen .= "Initiatoren: " . $alter_eintrag->initiatorInnen . " => " . $daten->initiatorInnen . "\n";
            if ($alter_eintrag->gestellt_von != $daten->gestellt_von) $aenderungen .= "Gestellt von: " . $alter_eintrag->gestellt_von . " => " . $daten->gestellt_von . "\n";
            if ($alter_eintrag->gestellt_am != $daten->gestellt_am) $aenderungen .= "Gestellt am: " . $alter_eintrag->gestellt_am . " => " . $daten->gestellt_am . "\n";
            if ($alter_eintrag->antrags_nr != $daten->antrags_nr) $aenderungen .= "Antrags-Nr: " . $alter_eintrag->antrags_nr . " => " . $daten->antrags_nr . "\n";
            if ($alter_eintrag->erledigt_am != $daten->erledigt_am) $aenderungen .= "Erledigt am: " . $alter_eintrag->erledigt_am . " => " . $daten->erledigt_am . "\n";
            if ($alter_eintrag->referat != $daten->referat) $aenderungen .= "Referat: " . $alter_eintrag->referat . " => " . $daten->referat . "\n";
            if ($alter_eintrag->referat_id != $daten->referat_id) $aenderungen .= "Referats-ID: " . $alter_eintrag->referat_id . " => " . $daten->referat_id . "\n";
            if ($alter_eintrag->ba_nr != $daten->ba_nr) $aenderungen .= "Bezirksausschuss: " . $alter_eintrag->ba_nr . " => " . $daten->ba_nr . "\n";
            if ($alter_eintrag->wahlperiode != $daten->wahlperiode) $aenderungen .= "Wahlperiode: " . $alter_eintrag->wahlperiode . " => " . $daten->wahlperiode . "\n";
            if ($aenderungen != "") $changed = true;
        }

        if ($changed) {
            if ($aenderungen == "") $aenderungen = "Neu angelegt\n";

            echo "Antrag $id: Verändert: " . $aenderungen . "\n";

            if ($alter_eintrag) {
                $alter_eintrag->copyToHistory();
                $alter_eintrag->setAttributes($daten->getAttributes(), false);

                // Leere Seiten ignorieren
                if ($alter_eintrag->wahlperiode === "" && $alter_eintrag->betreff === "" && $alter_eintrag->status === "") {
                    echo "Antrag $id ist leer";
                    return null;
                }

                if (!$alter_eintrag->save()) {
                    RISTools::report_ris_parser_error("Stadtratsantrag Fehler 1", "Antrag $id\n" . print_r($alter_eintrag->getErrors(), true));
                    throw new \Exception("StadtratAntrag 1");
                }
                $daten = $alter_eintrag;
            } else {
                if (!$daten->save()) {
                    RISTools::report_ris_parser_error("Stadtratsantrag Fehler 2", "Antrag $id\n" . print_r($daten->getErrors(), true));
                    throw new \Exception("StadtratAntrag 2");
                }
            }

            $daten->resetPersonen();
        }

        foreach ($parsed->dokumentLinks as $dok) {
            $aenderungen .= Dokument::create_if_necessary(Dokument::$TYP_STADTRAT_ANTRAG, $daten, $dok);
        }

        if ($aenderungen != "") {
            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = $daten->ba_nr;
            $aend->typ         = RISAenderung::$TYP_STADTRAT_ANTRAG;
            $aend->datum       = new CDbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();

            /** @var Antrag $antrag */
            $antrag                         = Antrag::model()->findByPk($id);
            $antrag->datum_letzte_aenderung = new CDbExpression('NOW()'); // Auch bei neuen Dokumenten
            $antrag->save();
            $antrag->rebuildVorgaenge();
        }

        return $daten;
    }


    public function parseAll(): void
    {
        $first = true;
        for ($i = static::$MAX_OFFSET; $i >= 0; $i -= 10) {
            if (SITE_CALL_MODE != "cron") echo (static::$MAX_OFFSET - $i) . " / " . static::$MAX_OFFSET . "\n";
            $this->parseSeite($i, $first);
            $first = false;
        }
    }

    public function parseUpdate(): void
    {
        $loaded_ids = [];
        echo "Updates: Stadtratsanträge\n";

        for ($i = static::$MAX_OFFSET_UPDATE; $i >= 0; $i -= 10) {
            $ids        = $this->parseSeite($i, false);
            $loaded_ids = array_merge($loaded_ids, array_map("IntVal", $ids));
        }

        $crit            = new CDbCriteria();
        $crit->condition = "typ='" . addslashes(Antrag::$TYP_STADTRAT_ANTRAG) . "' AND status != 'erledigt' AND gestellt_am > NOW() - INTERVAL 2 YEAR AND ((TO_DAYS(bearbeitungsfrist)-TO_DAYS(CURRENT_DATE()) < 14 AND TO_DAYS(bearbeitungsfrist)-TO_DAYS(CURRENT_DATE()) > -14) OR ((TO_DAYS(CURRENT_DATE()) - TO_DAYS(gestellt_am)) % 3) = 0)";
        if (count($loaded_ids) > 0) $crit->addNotInCondition("id", $loaded_ids);

        /** @var array|Antrag[] $antraege */
        $antraege = Antrag::model()->findAll($crit);
        foreach ($antraege as $antrag) $this->parse($antrag->id);
    }

    public function parseQuickUpdate(): void
    {
        $loaded_ids = [];
        echo "Updates (quick): Stadtratsanträge\n";

        for ($i = 0; $i <= 3; $i++) {
            $ids        = $this->parseSeite($i * 10, false);
            $loaded_ids = array_merge($loaded_ids, array_map("IntVal", $ids));
        }
    }


    /**
     * @return StadtratsantragListEntry[]
     * @throws ParsingException
     */
    public function parseMonth(int $year, int $month): array
    {
        $from = new \DateTime($year . '-' . $month . '-1');
        $to = (clone $from)->modify('last day of this month');

        $html = $this->browserBasedDowloader->downloadDocumentTypeListForPeriod(BrowserBasedDowloader::DOCUMENT_STADTRAT_ANTRAG, $from, $to);

        preg_match_all('/<li.*<\/li>/siuU', $html, $matches);
        $parsedObjects = [];
        foreach ($matches[0] as $match) {
            $obj = StadtratsantragListEntry::parseFromHtml($match);
            if ($obj) {
                $parsedObjects[] = $obj;
            }
        }

        echo count($parsedObjects) . " Stadtratsanträge gefunden\n";

        foreach ($parsedObjects as $object) {
            $this->parse($object->id);
        }

        return $parsedObjects;
    }
}
