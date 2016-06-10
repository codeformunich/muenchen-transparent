<?php

class BAInitiativeParser extends RISParser
{
    private static $MAX_OFFSET        = 5500;
    private static $MAX_OFFSET_UPDATE = 200;

    public function parse($antrag_id)
    {
        $antrag_id = IntVal($antrag_id);

        if (SITE_CALL_MODE != "cron") echo "- Initiative $antrag_id\n";
	if ($antrag_id == 0) {
                RISTools::report_ris_parser_error("Fehler BAInitiativeParser", "Initiative-ID 0\n" . print_r(debug_backtrace(), true));
                return;
        }

        $html_details   = RISTools::load_file(RIS_BA_BASE_URL . "ba_initiativen_details.jsp?Id=$antrag_id");
        $html_dokumente = RISTools::load_file(RIS_BA_BASE_URL . "ba_initiativen_dokumente.jsp?Id=$antrag_id");
        //$html_ergebnisse = load_file(RIS_BA_BASE_URL . "/RII/RII/ris_antrag_ergebnisse.jsp?risid=" . $antrag_id);

        $daten                         = new Antrag();
        $daten->id                     = $antrag_id;
        $daten->datum_letzte_aenderung = new CDbExpression('NOW()');
        $daten->typ                    = Antrag::$TYP_BA_INITIATIVE;

        $dokumente = [];
        //$ergebnisse = array();

        preg_match("/<h3.*>.* +(.*)<\/h3/siU", $html_details, $matches);
        if (count($matches) == 2) $daten->antrags_nr = Antrag::cleanAntragNr($matches[1]);;

        $dat_details = explode("<h3 class=\"introheadline\">BA-Initiativen-Nummer", $html_details);
        $dat_details = explode("<div class=\"formularcontainer\">", $dat_details[1]);
        preg_match_all("/class=\"detail_row\">.*detail_label\">(.*)<\/d.*detail_div\">(.*)<\/div/siU", $dat_details[0], $matches);

        $betreff_gefunden = false;
        for ($i = 0; $i < count($matches[1]); $i++) switch (trim($matches[1][$i])) {
            case "Betreff:":
                $betreff_gefunden = true;
                $daten->betreff   = html_entity_decode($this->text_simple_clean($matches[2][$i]), ENT_COMPAT, "UTF-8");
                break;
            case "Status:":
                $daten->status = $this->text_simple_clean($matches[2][$i]);
                break;
            case "Bearbeitung:":
                $daten->bearbeitung = trim(strip_tags($matches[2][$i]));
                break;
        }

        if (!$betreff_gefunden) {
            RISTools::report_ris_parser_error("Fehler BAInitiativeParser", "Kein Betreff\n" . $html_details);
            throw new Exception("Betreff nicht gefunden");
        }


        $dat_details = explode("<div class=\"detailborder\">", $html_details);
        $dat_details = explode("<!-- seitenfuss -->", $dat_details[1]);

        preg_match_all("/<span class=\"itext\">(.*)<\/span.*detail_div_(left|right|left_long)\">(.*)<\/div/siU", $dat_details[0], $matches);
        for ($i = 0; $i < count($matches[1]); $i++) if ($matches[3][$i] != "&nbsp;") switch ($matches[1][$i]) {
            case "Zust&auml;ndiges Referat:":
                $daten->referat = $matches[3][$i];
                break;
            case "Gestellt am:":
                $daten->gestellt_am = $this->date_de2mysql($matches[3][$i]);
                break;
            case "Wahlperiode:":
                $daten->wahlperiode = $matches[3][$i];
                break;
            case "Bearbeitungsfrist:":
                $daten->bearbeitungsfrist = $this->date_de2mysql($matches[3][$i]);
                break;
            case "Registriert am:":
                $daten->registriert_am = $this->date_de2mysql($matches[3][$i]);
                break;
            case "Bezirksausschuss:":
                $daten->ba_nr = IntVal($matches[3][$i]);
                break;
            case "Typ:":
                $daten->antrag_typ = strip_tags($matches[3][$i]);
                break;
            case "TO aufgenommen am:":
                $daten->initiative_to_aufgenommen = $this->date_de2mysql($matches[3][$i]);
                break;
        }
        if ($daten->wahlperiode == "") $daten->wahlperiode = "?";

        preg_match_all("/<li><span class=\"iconcontainer\">.*title=\"([^\"]+)\"[^>]+href=\"(.*)\".*>(.*)<\/a>/siU", $html_dokumente, $matches);
        for ($i = 0; $i < count($matches[1]); $i++) {
            $dokumente[] = [
                "url"        => $matches[2][$i],
                "name"       => $matches[3][$i],
                "name_title" => $matches[1][$i],
            ];
        }

        /*
        $dat_ergebnisse = explode("<!-- tabellenkopf -->", $html_ergebnisse);
        $dat_ergebnisse = explode("<!-- tabellenfuss -->", $dat_ergebnisse[1]);
        preg_match_all("<tr>.*bghell  tdborder\"><a.*\">(.*)<\/a>.*
        */

        if ($daten->ba_nr == 0) {
            echo "BA-Initiative $antrag_id: " . "Keine BA-Angabe";
            $GLOBALS["RIS_PARSE_ERROR_LOG"][] = "Keine BA-Angabe (Initiative): $antrag_id";
            return;
        }

        $aenderungen = "";

        /** @var Antrag $alter_eintrag */
        $alter_eintrag = Antrag::model()->findByPk($antrag_id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($alter_eintrag->betreff != $daten->betreff) $aenderungen .= "Betreff: " . $alter_eintrag->betreff . " => " . $daten->betreff . "\n";
            if ($alter_eintrag->bearbeitungsfrist != $daten->bearbeitungsfrist) $aenderungen .= "Bearbeitungsfrist: " . $alter_eintrag->bearbeitungsfrist . " => " . $daten->bearbeitungsfrist . "\n";
            if ($alter_eintrag->status != $daten->status) $aenderungen .= "Status: " . $alter_eintrag->status . " => " . $daten->status . "\n";
            if ($alter_eintrag->fristverlaengerung != $daten->fristverlaengerung) $aenderungen .= "Fristverlängerung: " . $alter_eintrag->fristverlaengerung . " => " . $daten->fristverlaengerung . "\n";
            if ($alter_eintrag->initiative_to_aufgenommen != $daten->initiative_to_aufgenommen) $aenderungen .= "In TO Aufgenommen: " . $alter_eintrag->initiative_to_aufgenommen . " => " . $daten->initiative_to_aufgenommen . "\n";
            if ($aenderungen != "") $changed = true;
            if ($alter_eintrag->wahlperiode == "") $alter_eintrag->wahlperiode = "?";
        }

        if ($changed) {
            if ($aenderungen == "") $aenderungen = "Neu angelegt\n";

            echo "BA-Initiative $antrag_id: Verändert: " . $aenderungen . "\n";

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

        foreach ($dokumente as $dok) {
            $aenderungen .= Dokument::create_if_necessary(Dokument::$TYP_BA_INITIATIVE, $daten, $dok);
        }

        if ($aenderungen != "") {
            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = $daten->ba_nr;
            $aend->typ         = RISAenderung::$TYP_BA_INITIATIVE;
            $aend->datum       = new CDbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();

            /** @var Antrag $antrag */
            $antrag                         = Antrag::model()->findByPk($antrag_id);
            $antrag->datum_letzte_aenderung = new CDbExpression('NOW()'); // Auch bei neuen Dokumenten
            $antrag->save();
            $antrag->rebuildVorgaenge();
        }
    }

    public function parseSeite($seite, $first)
    {
        if (SITE_CALL_MODE != "cron") echo "BA-Initiativen Seite $seite\n";
        $text = RISTools::load_file(RIS_BA_BASE_URL . "ba_initiativen.jsp?Trf=n&Start=$seite");

        $txt = explode("<!-- tabellenkopf -->", $text);
        $txt = explode("<div class=\"ergebnisfuss\">", $txt[1]);
        preg_match_all("/ba_initiativen_details\.jsp\?Id=([0-9]+)[\"'& ]/siU", $txt[0], $matches);

        if ($first && count($matches[1]) > 0) RISTools::report_ris_parser_error("BA-Initiativen VOLL", "Erste Seite voll: $seite");

        for ($i = count($matches[1]) - 1; $i >= 0; $i--) try {
            $this->parse($matches[1][$i]);
        } catch (Exception $e) {
            echo " EXCEPTION! " . $e . "\n";
        }
        return $matches[1];
    }

    public function parseAlle()
    {
        $anz   = static::$MAX_OFFSET;
        $first = true;
        for ($i = $anz; $i >= 0; $i -= 10) {
            if (SITE_CALL_MODE != "cron") echo ($anz - $i) . " / $anz\n";
            $this->parseSeite($i, $first);
            $first = false;
        }
    }


    public function parseUpdate()
    {
        echo "Updates: BA-Initiativen\n";
        $loaded_ids = [];
        $anz        = static::$MAX_OFFSET_UPDATE;
        for ($i = $anz; $i >= 0; $i -= 10) {
            $ids        = $this->parseSeite($i, false);
            $loaded_ids = array_merge($loaded_ids, array_map("IntVal", $ids));
        }
    }

    public function parseQuickUpdate()
    {

    }

}
