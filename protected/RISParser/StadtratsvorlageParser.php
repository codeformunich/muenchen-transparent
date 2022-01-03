<?php

class StadtratsvorlageParser extends RISParser
{
    private BrowserBasedDowloader $browserBasedDowloader;
    private CurlBasedDownloader $curlBasedDownloader;
    private StadtratsantragParser $stadtratsantragParser;

    public function __construct(?BrowserBasedDowloader $browserBasedDowloader = null, ?CurlBasedDownloader $curlBasedDownloader = null)
    {
        $this->browserBasedDowloader = $browserBasedDowloader ?: new BrowserBasedDowloader();
        $this->curlBasedDownloader = $curlBasedDownloader ?: new CurlBasedDownloader();
    }

    // Allows overriding the parser by a mock in PhpUnit
    public function getStadtratsantragParser(): StadtratsantragParser
    {
        if (!isset($this->stadtratsantragParser)) {
            $this->stadtratsantragParser = new StadtratsantragParser();
        }
        return $this->stadtratsantragParser;
    }

    public function setStadtratsantragParser(StadtratsantragParser $stadtratsantragParser): void
    {
        $this->stadtratsantragParser = $stadtratsantragParser;
    }

    public function parse(int $id): ?Antrag
    {
        if (SITE_CALL_MODE != "cron") echo "- Beschlussvorlage $id\n";

        $html = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'sitzungsvorlage/detail/' . $id);

        $parsed = StadtratsvorlageData::parseFromHtml($html);
        if ($parsed === null) {
            return null;
        }

        if ($parsed->hatAntragsliste) {
            $html = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'sitzungsvorlage/detail/antraege/' . $id);
            $parsed->parseAntraege($html);
        }

        // @TODO Parse https://risi.muenchen.de/risi/sitzungsvorlage/detail/antraege/6752652?13

        $daten = new Antrag();
        $daten->id = $id;
        $daten->datum_letzte_aenderung = new CDbExpression('NOW()');
        $daten->typ = Antrag::$TYP_STADTRAT_VORLAGE;
        $daten->betreff = $parsed->title;
        $daten->status = $parsed->status;
        $daten->gestellt_von = "";
        $daten->gestellt_am = $parsed->freigabe->format('Y-m-d');
        $daten->antrag_typ = $parsed->typ;
        $daten->bearbeitung = ""; // @TODO Does this exist in the new RIS?
        $daten->kurzinfo = $parsed->kurzinfo;
        $daten->initiatorInnen = "";
        $daten->referent = $parsed->referentIn ?? '';
        $daten->referat = $parsed->referatName ?? '';
        $daten->referat_id = $parsed->referatId;
        $daten->antrags_nr = $parsed->antragsnummer;
        $daten->wahlperiode = $parsed->wahlperiode;
        $daten->ba_nr = $parsed->baNr;

        $deleted = false; // @TODO Detect deleted documents
        $aenderungen = "";

        /** @var Antrag $alter_eintrag */
        $alter_eintrag = Antrag::model()->findByPk($id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($deleted) {
                $aenderungen = "gelöscht";
                $changed     = true;
            } else {
                if ($alter_eintrag->betreff != $daten->betreff) $aenderungen .= "Betreff: " . $alter_eintrag->betreff . " => " . $daten->betreff . "\n";
                if ($alter_eintrag->kurzinfo != $daten->kurzinfo) $aenderungen .= "Kurzinfo: " . $alter_eintrag->kurzinfo . " => " . $daten->kurzinfo . "\n";
                if ($alter_eintrag->bearbeitungsfrist != $daten->bearbeitungsfrist) $aenderungen .= "Bearbeitungsfrist: " . $alter_eintrag->bearbeitungsfrist . " => " . $daten->bearbeitungsfrist . "\n";
                if ($alter_eintrag->status != $daten->status) $aenderungen .= "Status: " . $alter_eintrag->status . " => " . $daten->status . "\n";
                if ($alter_eintrag->fristverlaengerung != $daten->fristverlaengerung) $aenderungen .= "Fristverlängerung: " . $alter_eintrag->fristverlaengerung . " => " . $daten->fristverlaengerung . "\n";
                if ($alter_eintrag->gestellt_von != $daten->gestellt_von) $aenderungen .= "Gestellt von: " . $alter_eintrag->gestellt_von . " => " . $daten->gestellt_von . "\n";
                if ($alter_eintrag->antrags_nr != $daten->antrags_nr) $aenderungen .= "Vorlagen-Nr: " . $alter_eintrag->antrags_nr . " => " . $daten->antrags_nr . "\n";
                if ($alter_eintrag->ba_nr != $daten->ba_nr) $aenderungen .= "BA: " . $alter_eintrag->ba_nr . " => " . $daten->ba_nr . "\n";
                if (isset($daten->referat) && $alter_eintrag->referat != $daten->referat) $aenderungen .= "Referat: " . $alter_eintrag->referat . " => " . $daten->referat . "\n";
                if (isset($daten->referent) && $alter_eintrag->referent != $daten->referent) $aenderungen .= "Referent: " . $alter_eintrag->referent . " => " . $daten->referent . "\n";
                if ($alter_eintrag->referat_id != $daten->referat_id) $aenderungen .= "Referats-ID: " . $alter_eintrag->referat_id . " => " . $daten->referat_id . "\n";
                if ($aenderungen != "") $changed = true;
            }
        }

        if ($changed) {
            echo "Vorlage $id: Verändert\n";

            if ($alter_eintrag) {
                $alter_eintrag->copyToHistory();
                $alter_eintrag->setAttributes($daten->getAttributes());
                if (!$alter_eintrag->save(false)) {
                    echo "Vorlage 1\n";
                    var_dump($alter_eintrag->getErrors());
                    die("Fehler");
                }
                $daten = $alter_eintrag;

                if ($deleted) {
                    echo "Lösche";
                    foreach ($daten->dokumente as $dok) {
                        echo $dok->antrag_id . " => null\n";
                        $dok->antrag_id = null;
                        $dok->save();
                    }
                    foreach ($daten->ergebnisse as $erg) {
                        echo $erg->antrag_id . " => null\n";
                        $erg->antrag_id = null;
                        $erg->save();
                    }
                    Yii::app()->db->createCommand("UPDATE tagesordnungspunkte_history SET antrag_id = NULL WHERE antrag_id = " . IntVal($id))->execute();
                    Yii::app()->db->createCommand("UPDATE tagesordnungspunkte SET antrag_id = NULL WHERE antrag_id = " . IntVal($id))->execute();
                    Yii::app()->db->createCommand("DELETE FROM antraege_orte WHERE antrag_id = " . IntVal($id))->execute();
                    Yii::app()->db->createCommand("DELETE FROM antraege_vorlagen WHERE antrag1 = " . IntVal($id))->execute();

                    if (!$daten->delete()) {
                        RISTools::report_ris_parser_error("Vorlage: Nicht gelöscht", "VorlageParser 2\n" . print_r($daten->getErrors(), true));
                        die("Fehler");
                    }
                    $aend              = new RISAenderung();
                    $aend->ris_id      = $daten->id;
                    $aend->ba_nr       = NULL;
                    $aend->typ         = RISAenderung::$TYP_STADTRAT_VORLAGE;
                    $aend->datum       = new CDbExpression("NOW()");
                    $aend->aenderungen = $aenderungen;
                    $aend->save();
                    return null;
                }
            } elseif (!$deleted) {
                if (!$daten->save()) {
                    echo "Vorlage 2\n";
                    var_dump(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3));
                    var_dump($daten->getErrors());
                    die("Fehler");
                }
            }

            $daten->resetPersonen();
        }

        foreach ($parsed->dokumentLinks as $dok) {
            $aenderungen .= Dokument::create_if_necessary(Dokument::$TYP_STADTRAT_ANTRAG, $daten, $dok);
        }

        foreach ($parsed->antraege as $antragsLink) {
            /** @var Antrag $antrag */
            $antrag = Antrag::model()->findByPk($antragsLink->id);
            if (!$antrag) {
                $antrag = $this->getStadtratsantragParser()->parse($antragsLink->id);
            }
            if (!$antrag) if (Yii::app()->params['adminEmail'] != "") RISTools::report_ris_parser_error("Stadtratsvorlage - Zugordnungs Error", $id . " - " . $link);

            $sql = Yii::app()->db->createCommand();
            $sql->select("antrag2")->from("antraege_vorlagen")->where("antrag1 = " . IntVal($id) . " AND antrag2 = " . IntVal($antrag->id));
            $data = $sql->queryAll();
            if (count($data) == 0) {
                $daten->addAntrag($antrag);
                $aenderungen .= "Neuer Antrag zugeordnet: " . $antrag->id . "\n";
            }
        }

        if ($aenderungen != "") {
            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = $daten->ba_nr;
            $aend->typ         = RISAenderung::$TYP_STADTRAT_VORLAGE;
            $aend->datum       = new CDbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();

            /** @var Antrag $antrag */
            $antrag                         = Antrag::model()->findByPk($id);
            if ($antrag) {
                $antrag->datum_letzte_aenderung = new CDbExpression('NOW()'); // Auch bei neuen Dokumenten
                $antrag->save();
                $antrag->rebuildVorgaenge();
            }
        }

        return $daten;
    }


    public function parseAll(): void
    {
        $anz   = static::$MAX_OFFSET;
        $first = true;
        for ($i = $anz; $i >= 0; $i -= 10) {
            if (SITE_CALL_MODE != "cron") echo ($anz - $i) . " / $anz\n";
            $this->parseSeite($i, $first);
            $first = false;
        }

    }

    public function parseUpdate(): void
    {
        echo "Updates: Stadtratsvorlagen\n";
        $loaded_ids = [];
        for ($i = static::$MAX_OFFSET_UPDATE; $i >= 0; $i -= 10) {
            $ids        = $this->parseSeite($i, false);
            $loaded_ids = array_merge($loaded_ids, array_map("IntVal", $ids));
        }

        $crit            = new CDbCriteria();
        $crit->condition = "typ='" . addslashes(Antrag::$TYP_STADTRAT_VORLAGE) . "' AND status NOT IN ('Endgültiger Beschluss', 'abgeschlossen') AND gestellt_am > NOW() - INTERVAL 2 YEAR";
        if (count($loaded_ids) > 0) $crit->addNotInCondition("id", $loaded_ids);

        /** @var array|Antrag[] $antraege */
        $antraege = Antrag::model()->findAll($crit);
        foreach ($antraege as $antrag) $this->parse($antrag->id);
    }

    public function parseQuickUpdate(): void
    {

    }

    /**
     * @return StadtratsantragListEntry[]
     * @throws ParsingException
     */
    public function parseMonth(int $year, int $month): array
    {
        $from = new \DateTime($year . '-' . $month . '-1');
        $to = (clone $from)->modify('last day of this month');

        $html = $this->browserBasedDowloader->downloadDocumentTypeListForPeriod(BrowserBasedDowloader::DOCUMENT_SITZUNGSVORLAGEN, $from, $to);

        preg_match_all('/<li.*<\/li>/siuU', $html, $matches);
        $parsedObjects = [];
        foreach ($matches[0] as $match) {
            $obj = StadtratsantragListEntry::parseFromHtml($match);
            if ($obj) {
                $parsedObjects[] = $obj;
            }
        }

        echo count($parsedObjects) . " Beschlussvorlage gefunden\n";

        foreach ($parsedObjects as $object) {
            $this->parse($object->id);
        }

        return $parsedObjects;
    }

}
