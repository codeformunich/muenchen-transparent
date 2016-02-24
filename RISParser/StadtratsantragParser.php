<?php

namespace app\risparser;

use Yii;
use app\components\RISTools;
use app\models\Antrag;
use app\models\Dokument;
use app\models\RISAenderung;
use app\models\Referat;
use app\risparser\RISParser;

class StadtratsantragParser extends RISParser
{
    private static $MAX_OFFSET        = 17000;
    private static $MAX_OFFSET_UPDATE = 200;

    public function parse($antrag_id)
    {
        $antrag_id = IntVal($antrag_id);

        if (in_array($antrag_id, [3258272])) return;

        if (SITE_CALL_MODE != "cron") echo "- Antrag $antrag_id\n";

        $html_details   = RISTools::load_file("http://www.ris-muenchen.de/RII/RII/ris_antrag_detail.jsp?risid=" . $antrag_id);
        $html_dokumente = RISTools::load_file("http://www.ris-muenchen.de/RII/RII/ris_antrag_dokumente.jsp?risid=" . $antrag_id);
        //$html_ergebnisse = load_file("http://www.ris-muenchen.de/RII/RII/ris_antrag_ergebnisse.jsp?risid=" . $antrag_id);

        if (trim($html_details) == "" || trim($html_dokumente) == "") return;

        $daten                         = new Antrag();
        $daten->id                     = $antrag_id;
        $daten->datum_letzte_aenderung = new DbExpression('NOW()');
        $daten->typ                    = Antrag::$TYP_STADTRAT_ANTRAG;
        $daten->referent               = "";
        $daten->referat                = "";
        $daten->kurzinfo               = "";
        $daten->bearbeitung            = "";
        $daten->initiatorInnen         = "";

        $dokumente = [];
        // $ergebnisse = array();

        $dat_details = explode("<!-- bereichsbild, bereichsheadline, allgemeiner text -->", $html_details);
        if (!isset($dat_details[1])) {
            echo $antrag_id . " - " . "http://www.ris-muenchen.de/RII/RII/ris_antrag_detail.jsp?risid=" . $antrag_id . "\n";
            var_dump($dat_details);
            return;
        }
        $dat_details = explode("<!-- detailbereich -->", $dat_details[1]);

        preg_match("/<h3.*>.*&nbsp;(.*)<\/h3/siU", $dat_details[0], $matches);
        if (count($matches) == 2) $daten->antrags_nr = Antrag::cleanAntragNr($matches[1]);

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
            RISTools::send_email(Yii::$app->params['adminEmail'], "Fehler StadtratsantragParser", "Kein Betreff\n" . $html_details, null, "system");
            throw new Exception("Betreff nicht gefunden");
        }

        $dat_details = explode("<!-- details und tabelle -->", $html_details);
        $dat_details = explode("<!-- tabellenfuss -->", $dat_details[1]);

        preg_match_all("/detail_label_long\">(<span class=\"itext\">)?([^<].*)<\/.*detail_div_(left|right|left_long)\">(.*)<\/div/siU", $dat_details[0], $matches);
        for ($i = 0; $i < count($matches[2]); $i++) if ($matches[4][$i] != "&nbsp;") switch ($matches[2][$i]) {
            case "Typ:":
                $daten->antrag_typ = $matches[4][$i];
                break;
            case "Zust&auml;ndiges Referat:":
                $daten->referat    = $matches[4][$i];
                $ref               = Referat::getByHtmlName($matches[4][$i]);
                $daten->referat_id = ($ref ? $ref->id : null);
                break;
            case "Gestellt am:":
                $daten->gestellt_am = $this->date_de2mysql($matches[4][$i]);
                break;
            case "Wahlperiode:":
                $daten->wahlperiode = $matches[4][$i];
                break;
            case "Bearbeitungsfrist:":
                $daten->bearbeitungsfrist = $this->date_de2mysql($matches[4][$i]);
                break;
            case "Fristverl&auml;ngerung:":
                $daten->fristverlaengerung = $this->date_de2mysql($matches[4][$i]);
                break;
            case "Gestellt von:":
                $daten->gestellt_von = $matches[4][$i];
                break;
            case "Initiatoren:":
                if ($matches[4][$i] != "&nbsp;") $daten->initiatorInnen = $matches[4][$i];
                break;
            case "Erledigt am:":
                if ($matches[4][$i] != "&nbsp;") $daten->erledigt_am = $this->date_de2mysql($matches[4][$i]);
                break;
        }

        // Die erste Match-Gruppe enthält den Wert von title im <a>-tag, der zweite die URL und der Dritte den von <a>-tag umschlossenen Text
        preg_match_all("/<li><span class=\"iconcontainer\">.*title=\"([^\"]*)\"[^>]*href=\"(.*)\">(.*)<\/a>/siU", $html_dokumente, $matches);
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
        http://www.ris-muenchen.de/RII/RII/ris_antrag_ergebnisse.jsp?risid=6127
        */

        $aenderungen = "";

        /** @var Antrag $alter_eintrag */
        $alter_eintrag = Antrag::model()->findByPk($antrag_id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($alter_eintrag->bearbeitungsfrist != $daten->bearbeitungsfrist) $aenderungen .= "Bearbeitungsfrist: " . $alter_eintrag->bearbeitungsfrist . " => " . $daten->bearbeitungsfrist . "\n";
            if ($alter_eintrag->status != $daten->status) $aenderungen .= "Status: " . $alter_eintrag->status . " => " . $daten->status . "\n";
            if ($alter_eintrag->fristverlaengerung != $daten->fristverlaengerung) $aenderungen .= "Fristverlängerung: " . $alter_eintrag->fristverlaengerung . " => " . $daten->fristverlaengerung . "\n";
            if (isset($daten->initiatorInnen) && $alter_eintrag->initiatorInnen != $daten->initiatorInnen) $aenderungen .= "Initiatoren: " . $alter_eintrag->initiatorInnen . " => " . $daten->initiatorInnen . "\n";
            if ($alter_eintrag->gestellt_von != $daten->gestellt_von) $aenderungen .= "Gestellt von: " . $alter_eintrag->gestellt_von . " => " . $daten->gestellt_von . "\n";
            if ($alter_eintrag->antrags_nr != $daten->antrags_nr) $aenderungen .= "Antrags-Nr: " . $alter_eintrag->antrags_nr . " => " . $daten->antrags_nr . "\n";
            if ($alter_eintrag->erledigt_am != $daten->erledigt_am) $aenderungen .= "Erledigt am: " . $alter_eintrag->erledigt_am . " => " . $daten->erledigt_am . "\n";
            if ($alter_eintrag->referat != $daten->referat) $aenderungen .= "Referat: " . $alter_eintrag->referat . " => " . $daten->referat . "\n";
            if ($alter_eintrag->referat_id != $daten->referat_id) $aenderungen .= "Referats-ID: " . $alter_eintrag->referat_id . " => " . $daten->referat_id . "\n";
            if ($aenderungen != "") $changed = true;
        }

        if ($changed) {
            if ($aenderungen == "") $aenderungen = "Neu angelegt\n";

            echo "Antrag $antrag_id: Verändert: " . $aenderungen . "\n";

            if ($alter_eintrag) {
                $alter_eintrag->copyToHistory();
                $alter_eintrag->setAttributes($daten->getAttributes(), false);
                if (!$alter_eintrag->save()) {
                    RISTools::send_email(Yii::$app->params['adminEmail'], "Stadtratsantrag Fehler 1", "Antrag $antrag_id\n" . print_r($alter_eintrag->getErrors(), true) . "\n\n" . $html_details, null, "system");
                    throw new \Exception("StadtratAntrag 1");
                }
                $daten = $alter_eintrag;
            } else {
                if (!$daten->save()) {
                    RISTools::send_email(Yii::$app->params['adminEmail'], "Stadtratsantrag Fehler 2", "Antrag $antrag_id\n" . print_r($daten->getErrors(), true) . "\n\n" . $html_details, null, "system");
                    throw new \Exception("StadtratAntrag 2");
                }
            }

            $daten->resetPersonen();
        }

        foreach ($dokumente as $dok) {
            $aenderungen .= Dokument::create_if_necessary(Dokument::$TYP_STADTRAT_ANTRAG, $daten, $dok);
        }

        if ($aenderungen != "") {
            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = $daten->ba_nr;
            $aend->typ         = RISAenderung::$TYP_STADTRAT_ANTRAG;
            $aend->datum       = new DbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();

            /** @var Antrag $antrag */
            $antrag                         = Antrag::model()->findByPk($antrag_id);
            $antrag->datum_letzte_aenderung = new DbExpression('NOW()'); // Auch bei neuen Dokumenten
            $antrag->save();
            $antrag->rebuildVorgaenge();
        }
    }


    public function parseSeite($seite, $first)
    {
        $text = RISTools::load_file("http://www.ris-muenchen.de/RII/RII/ris_antrag_trefferliste.jsp?txtPosition=$seite");
        $txt  = explode("<!-- ergebnisreihen -->", $text);
        if (!isset($txt[1])) {
            if (SITE_CALL_MODE != "cron") echo "- nichts\n";
            return [];
        }
        $txt = explode("<div class=\"ergebnisfuss\">", $txt[1]);
        preg_match_all("/ris_antrag_detail.jsp\?risid=([0-9]+)[\"'& ]/siU", $txt[0], $matches);

        if ($first && count($matches[1]) > 0) RISTools::send_email(Yii::$app->params['adminEmail'], "Stadtratsantrag VOLL", "Erste Seite voll: $seite", null, "system");

        for ($i = count($matches[1]) - 1; $i >= 0; $i--) try {
            $this->parse($matches[1][$i]);
        } catch (Exception $e) {
            echo " EXCEPTION! " . $e . "\n";
        }
        return $matches[1];
    }

    public function parseAlle()
    {
        $first = true;
        for ($i = static::$MAX_OFFSET; $i >= 0; $i -= 10) {
            if (SITE_CALL_MODE != "cron") echo (static::$MAX_OFFSET - $i) . " / " . static::$MAX_OFFSET . "\n";
            $this->parseSeite($i, $first);
            $first = false;
        }
    }

    public function parseUpdate()
    {
        $loaded_ids = [];
        echo "Updates: Stadtratsanträge\n";

        for ($i = static::$MAX_OFFSET_UPDATE; $i >= 0; $i -= 10) {
            $ids        = $this->parseSeite($i, false);
            $loaded_ids = array_merge($loaded_ids, array_map("IntVal", $ids));
        }

        $crit            = new DbCriteria();
        $crit->condition = "typ='" . addslashes(Antrag::$TYP_STADTRAT_ANTRAG) . "' AND status != 'erledigt' AND gestellt_am > NOW() - INTERVAL 2 YEAR AND ((TO_DAYS(bearbeitungsfrist)-TO_DAYS(CURRENT_DATE()) < 14 AND TO_DAYS(bearbeitungsfrist)-TO_DAYS(CURRENT_DATE()) > -14) OR ((TO_DAYS(CURRENT_DATE()) - TO_DAYS(gestellt_am)) % 3) = 0)";
        if (count($loaded_ids) > 0) $crit->addNotInCondition("id", $loaded_ids);

        /** @var array|Antrag[] $antraege */
        $antraege = Antrag::model()->findAll($crit);
        foreach ($antraege as $antrag) $this->parse($antrag->id);
    }

    public function parseQuickUpdate()
    {
        $loaded_ids = [];
        echo "Updates (quick): Stadtratsanträge\n";

        for ($i = 0; $i <= 3; $i++) {
            $ids        = $this->parseSeite($i * 10, false);
            $loaded_ids = array_merge($loaded_ids, array_map("IntVal", $ids));
        }
    }
}
