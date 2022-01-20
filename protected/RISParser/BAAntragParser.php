<?php

class BAAntragParser extends RISParser
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

        $html = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'antrag/detail/' . $id);

        $parsed = AntragData::parseFromHtml($html);
        if ($parsed === null) {
            return null;
        }

        $daten = new Antrag();
        $daten->id = $id;
        $daten->datum_letzte_aenderung = new CDbExpression('NOW()');
        $daten->typ = Antrag::TYP_BA_ANTRAG;
        $daten->referent = "";
        $daten->kurzinfo = "";
        $daten->initiatorInnen = implode(', ', $parsed->initiativeNamen);
        $daten->gestellt_von = implode(', ', $parsed->gestelltVon);
        $daten->betreff = $parsed->title;
        $daten->antrags_nr = $parsed->antragsnummer;
        $daten->status = $parsed->status;
        $daten->bearbeitung = $parsed->bearbeitungsart ?: '';
        $daten->antrag_typ = $parsed->typ ?? '';
        $daten->referat = $parsed->referatName ?? '';
        $daten->referat_id = $parsed->referatId ?? '';
        $daten->gestellt_am = $parsed->gestelltAm?->format('Y-m-d');
        $daten->wahlperiode = $parsed->wahlperiode;
        $daten->bearbeitungsfrist = $parsed->bearbeitungsfrist?->format('Y-m-d');
        $daten->registriert_am = $parsed->registriertAm?->format('Y-m-d');
        $daten->erledigt_am = $parsed->erledigtAm?->format('Y-m-d');
        $daten->ba_nr = $parsed->baNr;

        $aenderungen = "";

        /** @var Antrag|null $alter_eintrag */
        $alter_eintrag = Antrag::model()->findByPk($id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($alter_eintrag->betreff != $daten->betreff) $aenderungen .= "Betreff: " . $alter_eintrag->betreff . " => " . $daten->betreff . "\n";
            if ($alter_eintrag->gestellt_am != $daten->gestellt_am) $aenderungen .= "Gestellt am: " . $alter_eintrag->gestellt_am . " => " . $daten->gestellt_am . "\n";
            if ($alter_eintrag->registriert_am != $daten->registriert_am) $aenderungen .= "Registriert am: " . $alter_eintrag->registriert_am . " => " . $daten->registriert_am . "\n";
            if ($alter_eintrag->bearbeitungsfrist != $daten->bearbeitungsfrist) $aenderungen .= "Bearbeitungsfrist: " . $alter_eintrag->bearbeitungsfrist . " => " . $daten->bearbeitungsfrist . "\n";
            if ($alter_eintrag->status != $daten->status) $aenderungen .= "Status: " . $alter_eintrag->status . " => " . $daten->status . "\n";
            if ($alter_eintrag->fristverlaengerung != $daten->fristverlaengerung) $aenderungen .= "Fristverlängerung: " . $alter_eintrag->fristverlaengerung . " => " . $daten->fristverlaengerung . "\n";
            if ($alter_eintrag->typ != $daten->typ) $aenderungen .= "Typ: " . $alter_eintrag->typ . " => " . $daten->typ . "\n";
            if ($alter_eintrag->referat != $daten->referat) $aenderungen .= "Referat: " . $alter_eintrag->referat . " => " . $daten->referat . "\n";
            if ($alter_eintrag->referat_id != $daten->referat_id) $aenderungen .= "Referats-ID: " . $alter_eintrag->referat_id . " => " . $daten->referat_id . "\n";
            if ($alter_eintrag->ba_nr != $daten->ba_nr) $aenderungen .= "Bezirksausschuss: " . $alter_eintrag->ba_nr . " => " . $daten->ba_nr . "\n";
            if ($alter_eintrag->wahlperiode != $daten->wahlperiode) $aenderungen .= "Wahlperiode: " . $alter_eintrag->wahlperiode . " => " . $daten->wahlperiode . "\n";
            if ($aenderungen != "") $changed = true;
        }

        if ($changed) {
            if ($aenderungen == "") $aenderungen = "Neu angelegt\n";

            echo "BA-Antrag $id: " . $aenderungen;

            if ($alter_eintrag) {
                $alter_eintrag->copyToHistory();
                $alter_eintrag->setAttributes($daten->getAttributes());
                if (!$alter_eintrag->save()) {
                    var_dump($alter_eintrag->getErrors());
                    die("Fehler");
                }
                $daten = $alter_eintrag;
            } else {
                if (!$daten->save()) {
                    var_dump($daten->getErrors());
                    die("Fehler");
                }
            }

            $daten->resetPersonen();
        }

        foreach ($parsed->dokumentLinks as $dok) {
            $aenderungen .= Dokument::create_if_necessary(Dokument::TYP_STADTRAT_ANTRAG, $daten, $dok);
        }

        if ($aenderungen !== "") {
            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = $daten->ba_nr;
            $aend->typ         = ($daten->typ == Antrag::TYP_BA_ANTRAG ? RISAenderung::TYP_BA_ANTRAG : RISAenderung::TYP_BUERGERVERSAMMLUNG_EMPFEHLUNG);
            $aend->datum       = new CDbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();

            /** @var Antrag $antrag */
            $antrag = Antrag::model()->findByPk($id);
            $antrag->datum_letzte_aenderung = new CDbExpression('NOW()'); // Auch bei neuen Dokumenten
            $antrag->save();
            $antrag->rebuildVorgaenge();
        }

        return $daten;
    }

    public function parseAll(): void
    {
        for ($year = 2020; $year <= date('Y'); $year++) {
            for ($month = 1; $month <= 12; $month++) {
                echo "Parsing: $month/$year\n";
                $this->parseMonth($year, $month);
            }
        }
    }

    public function parseUpdate(): void
    {
        echo "Updates: BA-Anträge (3 Monate)\n";

        $loaded_ids = [];
        for ($i = -3; $i >= 0; $i++) {
            $month = (new DateTime())->modify($i . ' month');
            $loaded_ids = array_merge($loaded_ids, $this->parseMonth(intval($month->format('Y')), intval($month->format('m'))));
        }

        $crit            = new CDbCriteria();
        $crit->condition = "typ='" . addslashes(Antrag::TYP_BA_ANTRAG) . "' AND status != 'erledigt' AND gestellt_am > NOW() - INTERVAL 2 YEAR AND ((TO_DAYS(bearbeitungsfrist)-TO_DAYS(CURRENT_DATE()) < 14 AND TO_DAYS(bearbeitungsfrist)-TO_DAYS(CURRENT_DATE()) > -14) OR ((TO_DAYS(CURRENT_DATE()) - TO_DAYS(gestellt_am)) % 3) = 0)";
        if (count($loaded_ids) > 0) $crit->addNotInCondition("id", $loaded_ids);

        /** @var Antrag[] $antraege */
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

        $html = $this->browserBasedDowloader->downloadDocumentTypeListForPeriod(BrowserBasedDowloader::DOCUMENT_BA_ANTRAG, $from, $to);

        $parsedObjects = StadtratsantragListEntry::parseHtmlList($html);


        echo count($parsedObjects) . " BA-Anträge gefunden\n";

        foreach ($parsedObjects as $object) {
            $this->parse($object->id);
        }

        return $parsedObjects;
    }
}
