<?php

class BAAntragParser extends RISParser
{
    private static $MAX_OFFSET        = 14000;
    private static $MAX_OFFSET_UPDATE = 200;

    public function parse($antrag_id)
    {
        $antrag_id = IntVal($antrag_id);

        if (SITE_CALL_MODE != "cron") echo "- Antrag $antrag_id\n";
	if ($antrag_id == 0) {
		RISTools::send_email(Yii::app()->params['adminEmail'], "Fehler BAAntragParser", "Antrag-ID 0\n" . print_r(debug_backtrace(), true), null, "system");
		return;
	}

        $html_details   = RISTools::load_file("http://www.ris-muenchen.de/RII/BA-RII/ba_antraege_details.jsp?Id=$antrag_id&selTyp=");
        $html_dokumente = RISTools::load_file("http://www.ris-muenchen.de/RII/BA-RII/ba_antraege_dokumente.jsp?Id=$antrag_id&selTyp=BA-Antrag");
        //$html_ergebnisse = load_file("http://www.ris-muenchen.de/RII/RII/ris_antrag_ergebnisse.jsp?risid=" . $antrag_id);

        $daten                         = new Antrag();
        $daten->id                     = $antrag_id;
        $daten->datum_letzte_aenderung = new CDbExpression('NOW()');

        $dokumente = [];
        //$ergebnisse = array();

        $dat_details = explode("<!-- bereichsbild, bereichsheadline, allgemeiner text -->", $html_details);
        $dat_details = explode("<!-- detailbereich -->", $dat_details[1]);
        preg_match_all("/class=\"detail_row\">.*detail_label\">(.*)<\/d.*detail_div\">(.*)<\/div/siU", $dat_details[0], $matches);

        $betreff_gefunden = false;
        for ($i = 0; $i < count($matches[1]); $i++) switch (trim($matches[1][$i])) {
            case "Betreff:":
                $betreff_gefunden = true;
                $daten->betreff   = $this->text_simple_clean($matches[2][$i]);
                break;
            case "Status:":
                $daten->status = $this->text_simple_clean($matches[2][$i]);
                break;
            case "Bearbeitung:":
                $daten->bearbeitung = trim(strip_tags($matches[2][$i]));
                break;
        }

        if (!$betreff_gefunden) {
            RISTools::send_email(Yii::app()->params['adminEmail'], "Fehler BAAntragParser", "Kein Betreff\n" . $html_details, null, "system");
            throw new Exception("Betreff nicht gefunden");
        }

        $dat_details = explode("<!-- bereichsbild, bereichsheadline, allgemeiner text -->", $html_details);
        $dat_details = explode("<!-- tabellenfuss -->", $dat_details[1]);

        preg_match("/<h3.*>(.*) +(.*)<\/h3/siU", $dat_details[0], $matches);
        if (count($matches) == 3) {
            $daten->antrags_nr = Antrag::cleanAntragNr($matches[2]);;
            switch ($matches[1]) {
                case "BA-Antrags-Nummer:":
                    $daten->typ = Antrag::$TYP_BA_ANTRAG;
                    break;
                case "BV-Empfehlungs-Nummer:":
                    $daten->typ = Antrag::$TYP_BV_EMPFEHLUNG;
                    break;
                default:
                    RISTools::send_email(Yii::app()->params['adminEmail'], "RIS: Unbekannter BA-Antrags-Typ: " . $antrag_id, $matches[1], null, "system");
                    die();
            }
        } else {
            RISTools::send_email(Yii::app()->params['adminEmail'], "RIS: Unbekannter BA-Antrags-Typ: " . $antrag_id, $dat_details[0], null, "system");
            die();
        }

        preg_match_all("/<span class=\"itext\">(.*)<\/span.*detail_div_(left|right|left_long)\">(.*)<\/div/siU", $dat_details[0], $matches);
        for ($i = 0; $i < count($matches[1]); $i++) if ($matches[3][$i] != "&nbsp;") switch ($matches[1][$i]) {
            case "Zust&auml;ndiges Referat:":
                $daten->referat    = $matches[3][$i];
                $ref               = Referat::getByHtmlName($matches[3][$i]);
                $daten->referat_id = ($ref ? $ref->id : null);
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
        }

        preg_match_all("/<li><span class=\"iconcontainer\">.*href=\"(.*)\"[^>]*title=\"([^\"]*)\">(.*)<\/a>/siU", $html_dokumente, $matches);
        for ($i = 0; $i < count($matches[1]); $i++) {
            $dokumente[] = [
                "url"        => $matches[1][$i],
                "name"       => $matches[3][$i],
                "name_title" => $matches[2][$i],
            ];
        }

        /*
        $dat_ergebnisse = explode("<!-- tabellenkopf -->", $html_ergebnisse);
        $dat_ergebnisse = explode("<!-- tabellenfuss -->", $dat_ergebnisse[1]);
        preg_match_all("<tr>.*bghell  tdborder\"><a.*\">(.*)<\/a>.*
        http://www.ris-muenchen.de/RII/RII/ris_antrag_ergebnisse.jsp?risid=6127
        */

        if (!($daten->ba_nr > 0)) {
            echo "BA-Antrag $antrag_id:" . "Keine BA-Angabe";
            $GLOBALS["RIS_PARSE_ERROR_LOG"][] = "Keine BA-Angabe (Antrag): $antrag_id";
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
            if ($alter_eintrag->typ != $daten->typ) $aenderungen .= "Typ: " . $alter_eintrag->typ . " => " . $daten->typ . "\n";
            if ($alter_eintrag->referat != $daten->referat) $aenderungen .= "Referat: " . $alter_eintrag->referat . " => " . $daten->referat . "\n";
            if ($alter_eintrag->referat_id != $daten->referat_id) $aenderungen .= "Referats-ID: " . $alter_eintrag->referat_id . " => " . $daten->referat_id . "\n";
            if ($aenderungen != "") $changed = true;
        }

        if ($changed) {
            if ($aenderungen == "") $aenderungen = "Neu angelegt\n";

            echo "BA-Antrag $antrag_id: " . $aenderungen;

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
            $dok_typ = ($daten->typ == Antrag::$TYP_BA_ANTRAG ? Dokument::$TYP_BA_ANTRAG : Dokument::$TYP_BV_EMPFEHLUNG);
            $aenderungen .= Dokument::create_if_necessary($dok_typ, $daten, $dok);
        }

        if ($aenderungen != "") {
            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = $daten->ba_nr;
            $aend->typ         = ($daten->typ == Antrag::$TYP_BA_ANTRAG ? RISAenderung::$TYP_BA_ANTRAG : RISAenderung::$TYP_BV_EMPFEHLUNG);
            $aend->datum       = new CDbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();

            /** @var Antrag $antrag */
            $antrag                         = Antrag::model()->findByPk($antrag_id);
            $antrag->datum_letzte_aenderung = new CDbExpression('NOW()'); // Auch bei neuen Dokumenten
            $antrag->save();
            $antrag->rebuildVorgaengeCache();
        }
    }

    public function parseSeite($seite, $first)
    {
        if (SITE_CALL_MODE != "cron") echo "BA-Anträge Seite $seite\n";
        $text = RISTools::load_file("http://www.ris-muenchen.de/RII/BA-RII/ba_antraege.jsp?Start=$seite");

        $txt = explode("<!-- tabellenkopf -->", $text);
        if (!isset($txt[1])) return [];

        $txt = explode("<div class=\"ergebnisfuss\">", $txt[1]);
        preg_match_all("/ba_antraege_details\.jsp\?Id=([0-9]+)[\"'& ]/siU", $txt[0], $matches);

        if ($first && count($matches[1]) > 0) RISTools::send_email(Yii::app()->params['adminEmail'], "BA-Anträge VOLL", "Erste Seite voll: $seite", null, "system");

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
        //$anz = 800;
        for ($i = $anz; $i >= 0; $i -= 10) {
            if (SITE_CALL_MODE != "cron") echo ($anz - $i) . " / $anz\n";
            $this->parseSeite($i, $first);
            $first = false;
        }
    }

    public function parseUpdate()
    {
        echo "Updates: BA-Anträge\n";
        $loaded_ids = [];

        $anz = static::$MAX_OFFSET_UPDATE;
        for ($i = $anz; $i >= 0; $i -= 10) {
            $ids        = $this->parseSeite($i, false);
            $loaded_ids = array_merge($loaded_ids, array_map("IntVal", $ids));
        }

        $crit            = new CDbCriteria();
        $crit->condition = "typ='" . addslashes(Antrag::$TYP_BA_ANTRAG) . "' AND status != 'erledigt' AND gestellt_am > NOW() - INTERVAL 2 YEAR AND ((TO_DAYS(bearbeitungsfrist)-TO_DAYS(CURRENT_DATE()) < 14 AND TO_DAYS(bearbeitungsfrist)-TO_DAYS(CURRENT_DATE()) > -14) OR ((TO_DAYS(CURRENT_DATE()) - TO_DAYS(gestellt_am)) % 3) = 0)";
        if (count($loaded_ids) > 0) $crit->addNotInCondition("id", $loaded_ids);

        /** @var array|Antrag[] $antraege */
        $antraege = Antrag::model()->findAll($crit);
        foreach ($antraege as $antrag) $this->parse($antrag->id);
    }

    public function parseQuickUpdate()
    {

    }

}
