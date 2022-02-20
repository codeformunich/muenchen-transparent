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

        $htmlFraktionen = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'person/detail/' . $id . '?tab=fraktionen', false, true);
        $htmlAusschuesse = $this->browserBasedDowloader->downloadPersonsMembershipList($id, BrowserBasedDowloader::MEMBERSHIP_TYPE_STR_AUSSCHUESSE);

        $parsed = StadtraetInnenData::parseFromHtml($htmlFraktionen, $htmlAusschuesse, $id);

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

            $daten->web = $alter_eintrag->web;
            $daten->twitter = $alter_eintrag->twitter;
            $daten->facebook = $alter_eintrag->facebook;
            $daten->abgeordnetenwatch = $alter_eintrag->abgeordnetenwatch;
            $daten->quellen = $alter_eintrag->quellen;
            $daten->geburtstag = ($alter_eintrag->geburtstag !== null && $alter_eintrag->geburtstag !== '0000-00-00' ? $alter_eintrag->geburtstag : null);
            $daten->geschlecht = $alter_eintrag->geschlecht;
            $daten->beschreibung = $alter_eintrag->beschreibung;
            $daten->beruf = $alter_eintrag->beruf;
            $daten->kontaktdaten = $alter_eintrag->kontaktdaten;
            $daten->created = $alter_eintrag->created;
            $daten->modified = date("Y-m-d H:i:s");
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

        GremienmitgliedschaftData::setGremienmitgliedschaftenToPerson($daten, $parsed->fraktionsMitgliedschaften, Gremium::TYPE_STR_FRAKTION, null);
        GremienmitgliedschaftData::setGremienmitgliedschaftenToPerson($daten, $parsed->ausschussMitgliedschaften, Gremium::TYPE_STR_AUSSCHUSS, null);


        if ($aenderungen != "") echo "StadträtIn $id: Verändert: " . $aenderungen . "\n";

        if ($aenderungen != "") {
            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = null;
            $aend->typ         = RISAenderung::TYP_STADTRAETIN;
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
