<?php

class StadtratsvorlageParser extends RISParser
{
    private static $MAX_OFFSET        = 34000;
    private static $MAX_OFFSET_UPDATE = 400;

    public function parse($vorlage_id)
    {
        if (SITE_CALL_MODE != "cron") echo "- Beschlussvorlage $vorlage_id\n";

        $html_details    = RISTools::load_file(RIS_BASE_URL . "ris_vorlagen_detail.jsp?risid=" . $vorlage_id);
        $html_dokumente  = RISTools::load_file(RIS_BASE_URL . "ris_vorlagen_dokumente.jsp?risid=" . $vorlage_id);
        $html_ergebnisse = RISTools::load_file(RIS_BASE_URL . "ris_vorlagen_ergebnisse.jsp?risid=" . $vorlage_id);

        $daten                         = new Antrag();
        $daten->id                     = $vorlage_id;
        $daten->datum_letzte_aenderung = new CDbExpression('NOW()');
        $daten->typ                    = Antrag::$TYP_STADTRAT_VORLAGE;
        $daten->antrag_typ             = "";
        $daten->gestellt_von           = "";
        $daten->antrag_typ             = "";
        $daten->bearbeitung            = "";
        $daten->initiatorInnen         = "";
        $daten->referent               = "";
        $daten->referat                = "";

        $dokumente  = [];
        $ergebnisse = [];

        if (strpos($html_details, "ris_vorlagen_kurzinfo.jsp?risid=$vorlage_id")) {
            $html_kurzinfo = RISTools::load_file(RIS_BASE_URL . "ris_vorlagen_kurzinfo.jsp?risid=" . $vorlage_id);
            $txt           = explode("introtext_border\">", $html_kurzinfo);
            if (count($txt) < 2) {
                RISTools::report_ris_parser_error("Vorlage: kein introtext_border", $vorlage_id . "\n" . $html_kurzinfo);
                return;
            }
            $txt             = explode("</div>", $txt[1]);
            $daten->kurzinfo = trim(str_replace(["<br />", "<p>", "</p>"], ["", "", ""], $txt[0]));
        }

        $dat_details = explode("<!-- bereichsbild, bereichsheadline, allgemeiner text -->", $html_details);
        if (count($dat_details) == 1) {
            RISTools::report_ris_parser_error("Vorlage: Keine Details", $html_details);
            return;
        }

        preg_match("/Vorlagen\-Nr\.:&nbsp;([^<]*)</siU", $dat_details[1], $matches);
        $daten->antrags_nr = Antrag::cleanAntragNr($matches[1]);

        $dat_details      = explode("<!-- detailbereich -->", $dat_details[1]);
        $betreff_gefunden = false;

        preg_match_all("/class=\"detail_row\">.*detail_label\">(.*)<\/d.*detail_div\">(.*)<\/div/siU", $dat_details[0], $matches);
        for ($i = 0; $i < count($matches[1]); $i++) switch ($matches[1][$i]) {
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
            RISTools::report_ris_parser_error("Fehler StadtratsvorlageParser", "Kein Betreff\n" . $html_details);
            throw new Exception("Betreff nicht gefunden");
        }

        $dat_details = explode("<!-- details und tabelle -->", $html_details);
        $dat_details = explode("<!-- tabellenfuss -->", $dat_details[1]);

        preg_match_all("/label_long\">(<span class=\"itext\">)?([^<]*)(<\/span>)?<\/div.*detail_div_(left|right|left_long)\">(.*)<\/div/siU", $dat_details[0], $matches);

        for ($i = 0; $i < count($matches[1]); $i++) if ($matches[5][$i] != "&nbsp;") switch ($matches[2][$i]) {
            case "Typ:":
                $daten->antrag_typ = $matches[5][$i];
                break;
            case "Zust&auml;ndiges Referat:":
                $daten->referat    = $matches[5][$i];
                $ref               = Referat::getByHtmlName($matches[5][$i]);
                $daten->referat_id = ($ref ? $ref->id : null);
                break;
            case "Erstellt am:":
                $daten->gestellt_am = $this->date_de2mysql($matches[5][$i]);
                break;
            case "Wahlperiode:":
                $daten->wahlperiode = $matches[5][$i];
                break;
            case "Bearbeitungsfrist:":
                $daten->bearbeitungsfrist = $this->date_de2mysql($matches[5][$i]);
                break;
            case "Fristverl&auml;ngerung:":
                $daten->fristverlaengerung = $this->date_de2mysql($matches[5][$i]);
                break;
            case "Gestellt von:":
                $daten->gestellt_von = $matches[5][$i];
                break;
            case "Initiatoren:":
                if ($matches[5][$i] != "&nbsp;") $daten->initiatorInnen = $matches[5][$i];
                break;
            case "Stadtbezirk/e:":
                $daten->ba_nr = IntVal($matches[5][$i]);
                break;
            case "Referent/in:":
                $daten->referent = $matches[5][$i];
                break;
        }

        $geloescht = ($betreff_gefunden && $daten->wahlperiode == "" && $daten->status == "" && $daten->betreff == "");

        preg_match_all("/<li><span class=\"iconcontainer\">.*title=\"([^\"]+)\"[^>]*href=\"(.*)\">(.*)<\/a>/siU", $html_dokumente, $matches);
        for ($i = 0; $i < count($matches[1]); $i++) {
            $dokumente[] = [
                "url"        => $matches[2][$i],
                "name"       => $matches[3][$i],
                "name_title" => $matches[1][$i],
            ];
        }


        preg_match_all("/ris_antrag_detail\.jsp\?risid=([0-9]+)[\"'& ]/siU", $html_details, $matches);
        $antrag_links = (isset($matches[1]) && is_array($matches[1]) ? $matches[1] : []);

        $dat_ergebnisse = explode("<!-- tabellenkopf -->", $html_ergebnisse);
        if (count($dat_ergebnisse) > 1) {
            $dat_ergebnisse = explode("<!-- tabellenfuss -->", $dat_ergebnisse[1]);
            $gremien_link   = "<a class=\"link_bold\" href=\"(?<gremien_link>[^\"]*)\"";
            $sitzung_link   = "<a class=\"link_bold\" href=\"(?<sitzung_link>[^\"]*)\"";
            preg_match_all("/<tr[^>]*ergebnistab_tr[^>]*>.*${gremien_link}.*${sitzung_link}.*<\/tr>/siu", $dat_ergebnisse[0], $matches);
            $ba = $str = false;
            if (count($matches) == 0) $str = true;
            else for ($i = 0; $i < count($matches["gremien_link"]); $i++) {
                $link = $matches["gremien_link"][$i];
                if (strpos($link, "ris_gremien_detail.jsp?risid=") === 0) $str = true;
                if (strpos($link, "/RII/BA-RII/ba_gremien_details.jsp?Id=") === 0) {
                    $x = explode("/RII/BA-RII/ba_gremien_details.jsp?Id=", $link);
                    /** @var Gremium $g */
                    $g  = Gremium::model()->findByPk($x[1]);
                    $ba = $g->ba_nr;
                }
            }
            if ($ba > 0) $daten->ba_nr = IntVal($ba);
            else $daten->ba_nr = null;
        }

        $aenderungen = "";

        /** @var Antrag $alter_eintrag */
        $alter_eintrag = Antrag::model()->findByPk($vorlage_id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($geloescht) {
                $aenderungen = "gelöscht";
                $changed     = true;
            } else {
                if ($alter_eintrag->bearbeitungsfrist != $daten->bearbeitungsfrist) $aenderungen .= "Bearbeitungsfrist: " . $alter_eintrag->bearbeitungsfrist . " => " . $daten->bearbeitungsfrist . "\n";
                if ($alter_eintrag->status != $daten->status) $aenderungen .= "Status: " . $alter_eintrag->status . " => " . $daten->status . "\n";
                if ($alter_eintrag->fristverlaengerung != $daten->fristverlaengerung) $aenderungen .= "Fristverlängerung: " . $alter_eintrag->fristverlaengerung . " => " . $daten->fristverlaengerung . "\n";
                if (isset($daten->initiatorInnen) && $alter_eintrag->initiatorInnen != $daten->initiatorInnen) $aenderungen .= "Initiatoren: " . $alter_eintrag->initiatorInnen . " => " . $daten->initiatorInnen . "\n";
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
            echo "Vorlage $vorlage_id: Verändert: " . ($changed ? "Ja" : "Nein") . "\n";

            if ($alter_eintrag) {
                $alter_eintrag->copyToHistory();
                $alter_eintrag->setAttributes($daten->getAttributes());
                if (!$alter_eintrag->save(false)) {
                    echo "Vorlage 1\n";
                    var_dump($alter_eintrag->getErrors());
                    die("Fehler");
                }
                $daten = $alter_eintrag;

                if ($geloescht) {
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
                    Yii::app()->db->createCommand("UPDATE tagesordnungspunkte_history SET antrag_id = NULL WHERE antrag_id = " . IntVal($vorlage_id))->execute();
                    Yii::app()->db->createCommand("UPDATE tagesordnungspunkte SET antrag_id = NULL WHERE antrag_id = " . IntVal($vorlage_id))->execute();
                    Yii::app()->db->createCommand("DELETE FROM antraege_orte WHERE antrag_id = " . IntVal($vorlage_id))->execute();
                    Yii::app()->db->createCommand("DELETE FROM antraege_vorlagen WHERE antrag1 = " . IntVal($vorlage_id))->execute();

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
                    return;
                }
            } elseif (!$geloescht) {
                if (!$daten->save()) {
                    echo "Vorlage 2\n";
                    var_dump(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3));
                    var_dump($daten->getErrors());
                    die("Fehler");
                }
            }

            $daten->resetPersonen();
        }

        foreach ($dokumente as $dok) {
            $aenderungen .= Dokument::create_if_necessary(Dokument::$TYP_STADTRAT_VORLAGE, $daten, $dok);
        }

        foreach ($antrag_links as $link) {
            /** @var Antrag $antrag */
            $antrag = Antrag::model()->findByPk(IntVal($link));
            if (!$antrag) {
                $parser = new StadtratsantragParser();
                $parser->parse($link);

                $antrag = Antrag::model()->findByPk(IntVal($link));
            }
            if (!$antrag) if (Yii::app()->params['adminEmail'] != "") RISTools::report_ris_parser_error("Stadtratsvorlage - Zugordnungs Error", $vorlage_id . " - " . $link);

            $sql = Yii::app()->db->createCommand();
            $sql->select("antrag2")->from("antraege_vorlagen")->where("antrag1 = " . IntVal($vorlage_id) . " AND antrag2 = " . IntVal($antrag->id));
            $data = $sql->queryAll();
            if (count($data) == 0) {
                $daten->addAntrag($antrag);
                $aenderungen .= "Neuer Antrag zugeordnet: " . RIS_BASE_URL . "ris_antrag_detail.jsp?risid=$link\n";
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
            $antrag                         = Antrag::model()->findByPk($vorlage_id);
            $antrag->datum_letzte_aenderung = new CDbExpression('NOW()'); // Auch bei neuen Dokumenten
            $antrag->save();
            $antrag->rebuildVorgaenge();
        }
    }

    public function parseSeite($seite, $first)
    {
        if (SITE_CALL_MODE != "cron") echo "Seite: $seite\n";
        $text = RISTools::load_file(RIS_BASE_URL . "ris_vorlagen_trefferliste.jsp?txtSuchbegriff=&txtPosition=$seite");
        $txt  = explode("<!-- ergebnisreihen -->", $text);
        if (count($txt) == 1) return [];

        $txt = explode("<div class=\"ergebnisfuss\">", $txt[1]);
        preg_match_all("/ris_vorlagen_detail\.jsp\?risid=([0-9]+)[\"'& ]/siU", $txt[0], $matches);

        if ($first && count($matches[1]) > 0) RISTools::report_ris_parser_error("Stadtratsvorlagen VOLL", "Erste Seite voll: $seite");

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

    public function parseQuickUpdate()
    {

    }

}
