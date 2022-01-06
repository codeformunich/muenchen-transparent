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


        /** @var Tagesordnungspunkt[] $bisherige_tops */
        $bisherige_tops   = ($alter_eintrag ? $alter_eintrag->tagesordnungspunkte : []);
        $aenderungen_tops = "";
        $verwendete_top_ids = [];

        foreach (array_merge($parsed->agendaPublic, $parsed->agendaNonPublic) as $parsedItem) {
            $topNr = $parsedItem->topNr;

            $vorlagenId = null;
            if (count($parsedItem->vorlagenIds) > 1) {
                var_dump($parsedItem);
                die("More than one Vorlage");
            }
            foreach ($parsedItem->vorlagenIds as $vorlagenId) {
                $vorlage = Antrag::model()->findByPk($vorlagenId);
                if (!$vorlage) {
                    echo "Creating: $vorlagenId\n";
                    $this->getStadtratsvorlageParser()->parse($vorlagenId);
                }

                $vorlage = Antrag::model()->findByPk($vorlagenId);
                if (!$vorlage) {
                    $vorlagenId = null;
                }
            }

            $top = new Tagesordnungspunkt();
            $top->datum_letzte_aenderung = new CDbExpression("NOW()");
            $top->sitzungstermin_id = $id;
            $top->sitzungstermin_datum = substr($daten->termin, 0, 10);
            $top->top_pos = $parsedItem->position;
            $top->top_id = $parsedItem->id;
            $top->top_nr = $topNr;
            $top->antrag_id = $vorlagenId;
            $top->top_ueberschrift = ($parsedItem->isHeading ? 1 : 0);
            $top->status = ($parsedItem->public ? '' : Tagesordnungspunkt::STATUS_NONPUBLIC);
            $top->entscheidung = $parsedItem->decision ?? $parsedItem->disclosure;
            $top->top_betreff = $parsedItem->title;
            $top->gremium_id = $daten->gremium_id;
            $top->gremium_name = $daten->gremium->name;
            $top->beschluss_text = "";

            if (!is_null($vorlagenId)) {
                // @TODO Find out if there is still a "Beschlusstext" stored for the agenda
            }

            /** @var Tagesordnungspunkt|null $alter_top */
            if ($parsedItem->id) {
                $alter_top = Tagesordnungspunkt::model()->findByAttributes(["sitzungstermin_id" => $id, "top_id" => $parsedItem->id]);
            } else {
                $alter_top = Tagesordnungspunkt::model()->findByAttributes(["sitzungstermin_id" => $id, "top_betreff" => $parsedItem->title]);
            }

            $top_aenderungen = "";
            if ($alter_top) {
                if ($alter_top->sitzungstermin_id != $top->sitzungstermin_id) $top_aenderungen .= "Sitzung geändert: " . $alter_top->sitzungstermin_id . " => " . $top->sitzungstermin_id . "\n";
                if ($alter_top->sitzungstermin_datum != $top->sitzungstermin_datum) $top_aenderungen .= "Sitzungstermin geändert: " . $alter_top->sitzungstermin_datum . " => " . $top->sitzungstermin_datum . "\n";
                if ($alter_top->top_nr != $top->top_nr) $top_aenderungen .= "TOP geändert: " . $alter_top->top_nr . " => " . $top->top_nr . "\n";
                if ($alter_top->top_ueberschrift != $top->top_ueberschrift) $top_aenderungen .= "Bereich geändert: " . $alter_top->top_ueberschrift . " => " . $top->top_ueberschrift . "\n";
                if ($alter_top->top_betreff != $top->top_betreff) $top_aenderungen .= "Betreff geändert: " . $alter_top->top_betreff . " => " . $top->top_betreff . "\n";
                if ($alter_top->antrag_id != $top->antrag_id) $top_aenderungen .= "Antrag geändert: " . $alter_top->antrag_id . " => " . $top->antrag_id . "\n";
                if ($alter_top->gremium_id != $top->gremium_id) $top_aenderungen .= "Gremium geändert: " . $alter_top->gremium_id . " => " . $top->gremium_id . "\n";
                if ($alter_top->gremium_name != $top->gremium_name) $top_aenderungen .= "Gremium geändert: " . $alter_top->gremium_name . " => " . $top->gremium_name . "\n";
                if ($alter_top->entscheidung != $top->entscheidung) $top_aenderungen .= "Entscheidung: " . $alter_top->entscheidung . " => " . $top->entscheidung . "\n";
                if ($alter_top->beschluss_text != $top->beschluss_text) $top_aenderungen .= "Beschluss: " . $alter_top->beschluss_text . " => " . $top->beschluss_text . "\n";

                if ($top_aenderungen != "") {
                    $aend              = new RISAenderung();
                    $aend->ris_id      = $alter_top->id;
                    $aend->ba_nr       = NULL;
                    $aend->typ         = RISAenderung::$TYP_STADTRAT_ERGEBNIS;
                    $aend->datum       = new CDbExpression("NOW()");
                    $aend->aenderungen = $top_aenderungen;
                    $aend->save();

                    $aenderungen_tops .= "TOP geändert: " . $top->top_betreff . "\n   " . str_replace("\n", "\n   ", $top_aenderungen) . "\n";

                    $alter_top->copyToHistory();
                    $top->id = $alter_top->id;
                    $alter_top->setAttributes($top->getAttributes(), false);
                    if (!$alter_top->save()) {
                        echo "StadtratAntrag 1\n";
                        var_dump($alter_eintrag->getErrors());
                        die("Fehler");
                    }
                }
                $top = $alter_top;
            } else {
                $aenderungen .= "Neuer TOP: " . $topNr . " - " . $parsedItem->title . "\n";
                $top->save();
            }

            $verwendete_top_ids[] = $top->id;


            /*
             * @TODO Beschlüsse e.g. in https://risi.muenchen.de/risi/sitzung/detail/5656928/tagesordnung/oeffentlich
                // $aenderungen .= Dokument::create_if_necessary(Dokument::$TYP_STADTRAT_BESCHLUSS, $top, ["url" => $matches2["url"][0], "name" => $matches2["title"][0], "name_title" => ""]);
                / @var Dokument $dok /
                $dok = Dokument::model()->findByAttributes(["tagesordnungspunkt_id" => $top->id, "url" => $matches2["url"][0], "name" => $matches2["title"][0]]);
                if ($dok && $dok->tagesordnungspunkt_id != $top->id) {
                    echo "Korrgiere ID\n";
                    $dok->tagesordnungspunkt_id = $top->id;
                    $dok->save(false);
                }
            */
        }

        foreach ($bisherige_tops as $top) {
            //$top_key = ($top->status == "geheim" ? "geheim-" : "") . $top->top_nr . "-" . $top->top_betreff;
            if (!in_array($top->id, $verwendete_top_ids)) {
                $aenderungen_tops .= "TOP entfernt: " . $top->top_nr . ":" . $top->top_betreff . "\n";
                try {
                    $top->delete();
                } catch (CDbException $e) {
                    $str = "Vermutlich verwaiste Dokumente (war zuvor: \"" . $top->getName() . "\" in " . $daten->getLink() . ":\n";
                    /** @var Dokument[] $doks */
                    $doks = Dokument::model()->findAllByAttributes(["tagesordnungspunkt_id" => $top->id]);
                    foreach ($doks as $dok) {
                        $dok->tagesordnungspunkt_id = null;
                        $dok->save(false);
                        $str .= $dok->getOriginalLink() . "\n";
                    }
                    RISTools::send_email(Yii::app()->params["adminEmail"], "StadtratTermin Verwaist", $str, null, "system");
                    $top->delete();
                }
            }
        }

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
                    $aend->typ         = RISAenderung::$TYP_STADTRAT_TERMIN;
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
            $aend->typ         = RISAenderung::$TYP_STADTRAT_TERMIN;
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
        $anz   = TerminParser::$MAX_OFFSET;
        $first = true;
        for ($i = $anz; $i >= 0; $i -= 10) {
            if (SITE_CALL_MODE != "cron") echo ($anz - $i) . " / $anz\n";
            $this->parseSeite($i, $first, true);
            $first = false;
        }
    }

    public function parseUpdate(): void
    {
        echo "Updates: Stadtratstermin\n";
        $anz   = TerminParser::$MAX_OFFSET_UPDATE;
        $first = true;
        for ($i = 0; $i < $anz; $i += 10) {
            $this->parseSeite($anz - $i, $first, false);
            $first = false;
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

    }
}
