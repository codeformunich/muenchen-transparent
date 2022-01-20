<?php

class TerminParser extends RISParser
{
    private BrowserBasedDowloader $browserBasedDowloader;
    private CurlBasedDownloader $curlBasedDownloader;
    private StadtratsvorlageParser $stadtratsvorlageParser;
    private StadtratGremiumParser $stadtratGremiumParser;

    public function __construct(?BrowserBasedDowloader $browserBasedDowloader = null, ?CurlBasedDownloader $curlBasedDownloader = null)
    {
        $this->browserBasedDowloader = $browserBasedDowloader ?: new BrowserBasedDowloader();
        $this->curlBasedDownloader = $curlBasedDownloader ?: new CurlBasedDownloader();
    }

    // Allows overriding the parser by a mock in PhpUnit
    public function getStadtratsvorlageParser(): StadtratsvorlageParser
    {
        if (!isset($this->stadtratsvorlageParser)) {
            $this->stadtratsvorlageParser = new StadtratsvorlageParser();
        }
        return $this->stadtratsvorlageParser;
    }

    public function setStadtratsantragParser(StadtratsvorlageParser $stadtratsantragParser): void
    {
        $this->stadtratsvorlageParser = $stadtratsantragParser;
    }

    // Allows overriding the parser by a mock in PhpUnit
    public function getStadtratGremiumParser(): StadtratGremiumParser
    {
        if (!isset($this->stadtratGremiumParser)) {
            $this->stadtratGremiumParser = new StadtratGremiumParser();
        }
        return $this->stadtratGremiumParser;
    }

    public function setStadtratGremiumParser(StadtratGremiumParser $stadtratGremiumParser): void
    {
        $this->stadtratGremiumParser = $stadtratGremiumParser;
    }

    public function downloadCalendarEntryWithDependencies(int $id): ?CalendarData
    {
        $htmlEntry = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'sitzung/detail/' . $id);

        $parsed = CalendarData::parseFromHtml($htmlEntry, $id);
        if ($parsed === null) {
            return null;
        }

        if ($parsed->hasAgendaNonPublic) {
            $htmlNonPublic = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'sitzung/detail/' . $id . '/tagesordnung/nichtoeffentlich');
            $parsed->parseAgendaNonPublic($htmlNonPublic);
        }
        if ($parsed->hasAgendaPublic) {
            $htmlPublic = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'sitzung/detail/' . $id . '/tagesordnung/oeffentlich');
            $parsed->parseAgendaPublic($htmlPublic);
        }

        foreach(array_merge($parsed->agendaPublic, $parsed->agendaNonPublic) as $agendaItem) {
            if ($agendaItem->hasDecision && $agendaItem->id) {
                $htmlDecision = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'sitzung/top/' . $agendaItem->id . '/entscheidung');
                $agendaItem->parseDecision($htmlDecision);
            }
            if ($agendaItem->hasDisclosure && $agendaItem->id) {
                $htmlDisclosure = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'sitzung/top/' . $agendaItem->id . '/veroeffentlichung');
                $agendaItem->parseDisclosure($htmlDisclosure);
            }
        }

        return $parsed;
    }

    public function parse(int $id): ?Termin
    {
        if (SITE_CALL_MODE != "cron") echo "- Termin $id\n";

        $parsed = $this->downloadCalendarEntryWithDependencies($id);

        if ($parsed->organizationId) {
            $gr = Gremium::model()->findByPk($parsed->organizationId);
            if (!$gr) {
                echo "Lege Gremium an: " . $parsed->organizationId . "\n";
                $this->getStadtratGremiumParser()->parse($parsed->organizationId);
            }
        }

        $daten = new Termin();
        $daten->typ = Termin::TYP_AUTO;
        $daten->id = $id;
        $daten->datum_letzte_aenderung = new CDbExpression('NOW()');
        $daten->gremium_id = $parsed->organizationId;
        $daten->ba_nr = $parsed->baNr;
        $daten->sitzungsstand = $parsed->sitzungsstand ?? '';
        $daten->sitzungsort = $parsed->ort ?? '';
        $daten->referat = $parsed->referatName ?? '';
        $daten->referent = $parsed->referentInName ?? '';
        $daten->vorsitz = $parsed->vorsitzName ?? '';
        $daten->wahlperiode = $parsed->wahlperiode;
        $daten->status = $parsed->status;
        $daten->termin = $parsed->dateStart?->format('Y-m-d H:i:s');
        $daten->termin_next_id = $parsed->nextCalendarId;
        $daten->termin_prev_id = $parsed->prevCalendarId;

        $geloescht = false; // @TODO
        $aenderungen = "";

        /** @var Termin $alter_eintrag */
        $alter_eintrag = Termin::model()->findByPk($id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($geloescht) {
                $aenderungen = "gelöscht";
                $changed     = true;
            } else {
                if ($alter_eintrag->termin != $daten->termin) $aenderungen .= "Termin: " . $alter_eintrag->termin . " => " . $daten->termin . "\n";
                if ($alter_eintrag->ba_nr != $daten->ba_nr) $aenderungen .= "BA: " . $alter_eintrag->ba_nr . " => " . $daten->ba_nr . "\n";
                if ($alter_eintrag->gremium_id != $daten->gremium_id) $aenderungen .= "Gremium-ID: " . $alter_eintrag->gremium_id . " => " . $daten->gremium_id . "\n";
                if ($alter_eintrag->sitzungsort != $daten->sitzungsort) $aenderungen .= "Sitzungsort: " . $alter_eintrag->sitzungsort . " => " . $daten->sitzungsort . "\n";
                if ($alter_eintrag->termin_next_id != $daten->termin_next_id) $aenderungen .= "Nächster Termin: " . $alter_eintrag->termin_next_id . " => " . $daten->termin_next_id . "\n";
                if ($alter_eintrag->termin_prev_id != $daten->termin_prev_id) $aenderungen .= "Voriger Termin: " . $alter_eintrag->termin_prev_id . " => " . $daten->termin_prev_id . "\n";
                if ($alter_eintrag->wahlperiode != $daten->wahlperiode) $aenderungen .= "Wahlperiode: " . $alter_eintrag->wahlperiode . " => " . $daten->wahlperiode . "\n";
                if ($alter_eintrag->referat != $daten->referat) $aenderungen .= "Referat: " . $alter_eintrag->referat . " => " . $daten->referat . "\n";
                if ($alter_eintrag->referent != $daten->referent) $aenderungen .= "Referent: " . $alter_eintrag->referent . " => " . $daten->referent . "\n";
                if ($alter_eintrag->vorsitz != $daten->vorsitz) $aenderungen .= "Vorsitz: " . $alter_eintrag->vorsitz . " => " . $daten->vorsitz . "\n";
                if ($alter_eintrag->sitzungsstand != $daten->sitzungsstand) $aenderungen .= "Sitzungsstand: " . $alter_eintrag->sitzungsstand . " => " . $daten->sitzungsstand . "\n";
                if ($aenderungen != "") $changed = true;
            }
        }
        if (!$alter_eintrag) $daten->save();


        $calendarAgendaUpdater = new CalendarAgendaUpdater($this->getStadtratsvorlageParser());
        $calendarAgendaUpdater->setOldItems($alter_eintrag ? $alter_eintrag->tagesordnungspunkte : []);
        $aenderungen_tops = $calendarAgendaUpdater->updateAgendaToNewItems($daten, array_merge($parsed->agendaPublic, $parsed->agendaNonPublic));

        if ($aenderungen_tops != "") $changed = true;


        if ($changed) {
            if (!$alter_eintrag) $aenderungen = "Neu angelegt\n";
            $aenderungen .= $aenderungen_tops;

            echo "StR-Termin $id: Verändert: " . $aenderungen . "\n";

            if ($alter_eintrag) {
                $alter_eintrag->copyToHistory();
                $alter_eintrag->setAttributes($daten->getAttributes());
                if (!$alter_eintrag->save(false)) {
                    RISTools::report_ris_parser_error("Stadtratstermin: Nicht gespeichert", "StadtratTerminParser 1\n" . print_r($alter_eintrag->getErrors(), true));
                    die("Fehler");
                }
                $daten = $alter_eintrag;

                if ($geloescht) {
                    echo "Lösche";
                    if (!$daten->delete()) {
                        RISTools::report_ris_parser_error("Stadtratstermin: Nicht gelöscht", "StadtratTerminParser 2\n" . print_r($daten->getErrors(), true));
                        die("Fehler");
                    }
                    $aend              = new RISAenderung();
                    $aend->ris_id      = $daten->id;
                    $aend->ba_nr       = NULL;
                    $aend->typ         = RISAenderung::TYP_STADTRAT_TERMIN;
                    $aend->datum       = new CDbExpression("NOW()");
                    $aend->aenderungen = $aenderungen;
                    $aend->save();
                    return null;
                }

            } else {
                if (!$daten->save()) {
                    RISTools::report_ris_parser_error("Stadtratstermin: Nicht gespeichert", "StadtratTerminParser 3\n" . print_r($daten->getErrors(), true));
                    die("Fehler");
                }
            }
        }


        foreach ($parsed->dokumentLinks as $dok) {
            $aenderungen .= Dokument::create_if_necessary(Dokument::TYP_STADTRAT_TERMIN, $daten, $dok);
        }


        if ($aenderungen != "") {
            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = NULL;
            $aend->typ         = RISAenderung::TYP_STADTRAT_TERMIN;
            $aend->datum       = new CDbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();

            /** @var Termin $termin */
            $termin                         = Termin::model()->findByPk($id);
            $termin->datum_letzte_aenderung = new CDbExpression('NOW()'); // Auch bei neuen Dokumenten
            $termin->save();
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
        echo "Updates: Stadtratstermin\n";

        for ($i = -6; $i <= 3; $i++) {
            $month = (new DateTime())->modify($i . ' month');
            $this->parseMonth(intval($month->format('Y')), intval($month->format('m')));
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

        $html = $this->browserBasedDowloader->downloadDocumentTypeListForPeriod(BrowserBasedDowloader::DOCUMENT_SITZUNG, $from, $to);

        $parsedObjects = CalendarListEntry::parseHtmlList($html);
        echo count($parsedObjects) . " Termine gefunden\n";

        foreach ($parsedObjects as $object) {
            $this->parse($object->id);
        }

        return $parsedObjects;
    }

    public function parseQuickUpdate(): void
    {
        $lastMonth = (new DateTime())->modify('-1 month');
        $this->parseMonth(intval($lastMonth->format('Y')), intval($lastMonth->format('m')));

        $month = new DateTime();
        $this->parseMonth(intval($month->format('Y')), intval($month->format('m')));

        $nextMonth = (new DateTime())->modify('+1 month');
        $this->parseMonth(intval($nextMonth->format('Y')), intval($nextMonth->format('m')));
    }
}
