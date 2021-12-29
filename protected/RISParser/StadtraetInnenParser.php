<?php

class StadtraetInnenParser extends RISParser
{
    private BrowserBasedDowloader $browserBasedDowloader;
    private CurlBasedDownloader $curlBasedDownloader;

    private bool $antraege_alle = false;

    public function __construct(?BrowserBasedDowloader $browserBasedDowloader = null, ?CurlBasedDownloader $curlBasedDownloader = null)
    {
        $this->browserBasedDowloader = $browserBasedDowloader ?: new BrowserBasedDowloader();
        $this->curlBasedDownloader = $curlBasedDownloader ?: new CurlBasedDownloader();
    }

    public function setParseAlleAntraege(bool $set): void
    {
        $this->antraege_alle = $set;
    }

    public function parse_antraege($stadtraetIn_id, $seite)
    {
        $antr_text = RISTools::load_file(RIS_BASE_URL . "ris_antrag_trefferliste.jsp?nav=2&selWahlperiode=0&steller=$stadtraetIn_id&txtPosition=" . ($seite * 10));

        preg_match_all("/ris_antrag_detail\.jsp\?risid=(?<antrag_id>[0-9]+)[\"'& ]/siU", $antr_text, $matches);
        foreach ($matches["antrag_id"] as $antrag_id) try {
            Yii::app()->db->createCommand()->insert("antraege_stadtraetInnen", ["antrag_id" => $antrag_id, "stadtraetIn_id" => $stadtraetIn_id, "gefunden_am" => new CDbExpression("NOW()")]);
        } catch (Exception $e) {
        }
    }

    public function parse(int $id): StadtraetIn
    {
        if (SITE_CALL_MODE != "cron") echo "- StadträtIn $id\n";

        $htmlFraktionen = $this->curlBasedDownloader->loadUrl(RIS_BASE_URL . 'person/detail/' . $id . '?tab=fraktionen');
        $htmlAusschuesse = $this->curlBasedDownloader->loadUrl(RIS_BASE_URL . 'person/detail/' . $id . '?tab=strausschuesse');

        $parsed = StadtraetInnenData::parseFromHtml($htmlFraktionen, $htmlAusschuesse);

        $daten = new StadtraetIn();
        $daten->id = $id;
        $daten->referentIn = 0;
        $daten->beruf = '';
        $daten->beschreibung = '';
        $daten->quellen = '';
        $daten->name = $parsed->name;
        $daten->gewaehlt_am = $parsed->gewaehltAm?->format('Y-m-d');
        $daten->bio = $parsed->lebenslauf ?? '';

        $aenderungen = "";

        /** @var StadtraetIn $alter_eintrag */
        $alter_eintrag = StadtraetIn::model()->findByPk($id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($alter_eintrag->name != $daten->name) $aenderungen .= "Name: " . $alter_eintrag->name . " => " . $daten->name . "\n";
            if ($alter_eintrag->gewaehlt_am != $daten->gewaehlt_am) $aenderungen .= "Gewählt am: " . $alter_eintrag->gewaehlt_am . " => " . $daten->gewaehlt_am . "\n";
            if ($alter_eintrag->bio != $daten->bio) $aenderungen .= "Biografie: " . $alter_eintrag->bio . " => " . $daten->bio . "\n";
            if ($aenderungen != "") $changed = true;

            $daten->web               = $alter_eintrag->web;
            $daten->twitter           = $alter_eintrag->twitter;
            $daten->facebook          = $alter_eintrag->facebook;
            $daten->abgeordnetenwatch = $alter_eintrag->abgeordnetenwatch;
            $daten->quellen           = $alter_eintrag->quellen;
            $daten->geburtstag        = $alter_eintrag->geburtstag;
            $daten->geschlecht        = $alter_eintrag->geschlecht;
            $daten->beschreibung      = $alter_eintrag->beschreibung;
            $daten->beruf             = $alter_eintrag->beruf;
            $daten->kontaktdaten      = $alter_eintrag->kontaktdaten;
        }

        if ($changed) {
            if ($aenderungen == "") $aenderungen = "Neu angelegt\n";
        }

        if ($alter_eintrag) {
            $alter_eintrag->setAttributes($daten->getAttributes(), false);
            if (!$alter_eintrag->save()) {
                echo "StadträtInnen 1\n";
                var_dump($alter_eintrag->getErrors());
                die("Fehler");
            }
            $daten = $alter_eintrag;
        } else {
            if (!$daten->save()) {
                echo "StadträtInnen 2\n";
                var_dump($daten->getErrors());
                die("Fehler");
            }
        }

        foreach ($parsed->fraktionsMitgliedschaften as $fraktionMitgliedschaft) {
            $str_fraktion = new StadtraetInFraktion();
            $str_fraktion->datum_von = $fraktionMitgliedschaft->seit?->format('Y-m-d');
            $str_fraktion->datum_bis = $fraktionMitgliedschaft->bis?->format('Y-m-d');
            $str_fraktion->fraktion_id    = $fraktionMitgliedschaft->gremiumId;
            $str_fraktion->stadtraetIn_id = $id;
            $str_fraktion->wahlperiode    = $fraktionMitgliedschaft->wahlperiode;
            $str_fraktion->funktion       = $fraktionMitgliedschaft->funktion;
            $str_fraktion->mitgliedschaft = null;

            /** @var array|StadtraetInFraktion[] $bisherige_fraktionen */
            $bisherige_fraktionen = StadtraetInFraktion::model()->findAllByAttributes(["stadtraetIn_id" => $id]);
            /** @var null|StadtraetInFraktion $bisherige */

            $bisherige = null;
            foreach ($bisherige_fraktionen as $fr) {
                if ($fr->fraktion_id == $str_fraktion->fraktion_id && $fr->wahlperiode == $str_fraktion->wahlperiode && $fr->funktion == $str_fraktion->funktion) {
                    $bisherige = $fr;
                }
            }

            if ($bisherige === null) {
                $fraktion = Fraktion::model()->findByPk($str_fraktion->fraktion_id);
                if (is_null($fraktion)) {
                    $frakt_parser = new StadtratsfraktionParser();
                    $frakt_parser->parse($str_fraktion->fraktion_id, $str_fraktion->wahlperiode);
                }
                $str_fraktion->save();
                $aenderungen = "Neue Fraktionszugehörigkeit: " . $str_fraktion->fraktion->name . "\n";
            } else {
                if ($bisherige->wahlperiode != $fraktionMitgliedschaft->wahlperiode) $aenderungen .= "Neue Wahlperiode: " . $bisherige->wahlperiode . " => " . $fraktionMitgliedschaft->wahlperiode . "\n";
                if ($bisherige->funktion != $fraktionMitgliedschaft->funktion) $aenderungen .= "Neue Funktion in der Fraktion: " . $bisherige->funktion . " => " . $fraktionMitgliedschaft->funktion . "\n";
                //if ($bisherige->mitgliedschaft != $matches["mitgliedschaft"][$i]) $aenderungen .= "Mitgliedschaft in der Fraktion: " . $bisherige->mitgliedschaft . " => " . $matches["mitgliedschaft"][$i] . "\n";
                if ($bisherige->datum_von != $str_fraktion->datum_von) $aenderungen .= "Fraktionsmitgliedschaft Start: " . $bisherige->datum_von . " => " . $str_fraktion->datum_von . "\n";
                if ($bisherige->datum_bis != $str_fraktion->datum_bis) $aenderungen .= "Fraktionsmitgliedschaft Ende: " . $bisherige->datum_bis . " => " . $str_fraktion->datum_bis . "\n";
                $bisherige->setAttributes($str_fraktion->getAttributes());
                $bisherige->save();
            }
        }


        if ($aenderungen != "") echo "StadträtIn $id: Verändert: " . $aenderungen . "\n";

        if ($aenderungen != "") {
            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = null;
            $aend->typ         = RISAenderung::$TYP_STADTRAETIN;
            $aend->datum       = new CDbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();
        }

        /*
         * @TODO
        if ($this->antraege_alle) {
            $text = RISTools::load_file(RIS_BASE_URL . "ris_antrag_trefferliste.jsp?nav=2&selWahlperiode=0&steller=$id&txtPosition=0");
            if (preg_match("/Suchergebnisse:.* ([0-9]+)<\/p>/siU", $text, $matches)) {
                $seiten = Ceil($matches[1] / 10);
                for ($i = 0; $i < $seiten; $i++) $this->parse_antraege($id, $i);
            } else if (SITE_CALL_MODE != "cron") echo "Keine Anträge gefunden\n";
        } else for ($i = 0; $i < 2; $i++) {
            $this->parse_antraege($id, $i);
        }
        */

        return $daten;
    }


    public function parseAll(): void
    {
        $html = $this->browserBasedDowloader->downloadPersonList(BrowserBasedDowloader::PERSON_TYPE_STADTRAT);
        $entries = StadtraetInnenListEntry::parseHtmlList($html);

        echo count($entries) . " Stadtratsmitglieder gefunden\n";

        foreach ($entries as $entry) {
            $this->parse($entry->id);
        }
    }

    public function parseUpdate(): void
    {
        echo "Updates: StadträtInnen\n";
        $this->parseAll();
    }

    public function parseQuickUpdate(): void
    {

    }
}
