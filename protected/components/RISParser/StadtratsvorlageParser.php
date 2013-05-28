<?php

class StadtratsvorlageParser extends RISParser {

	public function parse($vorlage_id) {
		echo "- Beschlussvorlage $vorlage_id\n";

		$html_details = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_vorlagen_detail.jsp?risid=" . $vorlage_id);
		$html_dokumente = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_vorlagen_dokumente.jsp?risid=" . $vorlage_id);
		//$html_ergebnisse = load_file("http://www.ris-muenchen.de/RII2/RII/ris_unterlage_ergebnisse.jsp?risid=" . $vorlage_id);

		$daten = new Antrag();
		$daten->id = $vorlage_id;
		$daten->datum_letzte_aenderung = new CDbExpression('NOW()');
		$daten->typ = Antrag::$TYP_STADTRAT_VORLAGE;

		$dokumente = array();
		// $ergebnisse = array();

		if (mb_strpos($html_details, "ris_vorlagen_kurzinfo.jsp?risid=$vorlage_id")) {
			$html_kurzinfo = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_vorlagen_kurzinfo.jsp?risid=" . $vorlage_id);
			$txt = explode("introtext_border\">", $html_kurzinfo);
			$txt = explode("</div>", $txt[1]);
			$daten->kurzinfo = trim(str_replace(array("<br />", "<p>", "</p>"), array("", "", ""), $txt[0]));
		}

		$dat_details = explode("<!-- bereichsbild, bereichsheadline, allgemeiner text -->", $html_details);

		preg_match("/Vorlagen\-Nr\.:&nbsp;([^<]*)</siU", $dat_details[1], $matches);
		$daten->antrags_nr = trim($matches[1]);

		$dat_details = explode("<!-- detailbereich -->", $dat_details[1]);

		preg_match_all("/class=\"detail_row\">.*detail_label\">(.*)<\/d.*detail_div\">(.*)<\/div/siU", $dat_details[0], $matches);
		for ($i = 0; $i < count($matches[1]); $i++) switch ($matches[1][$i]) {
			case "Betreff:": $daten->betreff = $this->text_simple_clean($matches[2][$i]); break;
			case "Status:": $daten->status = $this->text_simple_clean($matches[2][$i]); break;
			case "Bearbeitung:": $daten->bearbeitung = trim(strip_tags($matches[2][$i])); break;
		}

		$dat_details = explode("<!-- details und tabelle -->", $html_details);
		$dat_details = explode("<!-- tabellenfuss -->", $dat_details[1]);

		preg_match_all("/label_long\">(<span class=\"itext\">)?([^<]*)(<\/span>)?<\/div.*detail_div_(left|right|left_long)\">(.*)<\/div/siU", $dat_details[0], $matches);

		for ($i = 0; $i < count($matches[1]); $i++) if ($matches[5][$i] != "&nbsp;") switch ($matches[2][$i]) {
			case "Typ:": $daten->antrag_typ = $matches[5][$i]; break;
			case "Zust&auml;ndiges Referat:": $daten->referat = $matches[5][$i]; break;
			case "Erstellt am:": $daten->gestellt_am = $this->date_de2mysql($matches[5][$i]); break;
			case "Wahlperiode:": $daten->wahlperiode = $matches[5][$i]; break;
			case "Bearbeitungsfrist:": $daten->bearbeitungsfrist = $this->date_de2mysql($matches[5][$i]); break;
			case "Fristverl&auml;ngerung:": $daten->fristverlaengerung = $this->date_de2mysql($matches[5][$i]); break;
			case "Gestellt von:": $daten->gestellt_von = $matches[5][$i]; break;
			case "Initiatoren:": if ($matches[5][$i] != "&nbsp;") $daten->initiatorInnen = $matches[5][$i]; break;
			case "Stadtbezirk/e:": $daten->ba_nr = IntVal($matches[5][$i]); break;
			case "Referent/in:": $daten->referent = $matches[5][$i]; break;
		}

		preg_match_all("/<li><span class=\"iconcontainer\">.*href=\"(.*)\">(.*)<\/a>/siU", $html_dokumente, $matches);
		for ($i = 0; $i < count($matches[1]); $i++) {
			$dokumente[] = array(
				"url" => $matches[1][$i],
				"name" => $matches[2][$i],
			);
		}


		preg_match_all("/ris_antrag_detail\.jsp\?risid=([0-9]+)[\"'& ]/siU", $html_details, $matches);
		$antrag_links = (isset($matches[1]) && is_array($matches[1]) ? $matches[1] : array());

		/*
		$dat_ergebnisse = explode("<!-- tabellenkopf -->", $html_ergebnisse);
		$dat_ergebnisse = explode("<!-- tabellenfuss -->", $dat_ergebnisse[1]);
		preg_match_all("<tr>.*bghell  tdborder\"><a.*\">(.*)<\/a>.*
		http://www.ris-muenchen.de/RII2/RII/ris_unterlage_ergebnisse.jsp?risid=6127
		*/

		$aenderungen = "";

		/** @var Antrag $alter_eintrag */
		$alter_eintrag = Antrag::model()->findByPk($vorlage_id);
		$changed = true;
		if ($alter_eintrag) {
			$changed = false;
			if ($alter_eintrag->bearbeitungsfrist != $daten->bearbeitungsfrist) $aenderungen .= "Bearbeitungsfrist: " . $alter_eintrag->bearbeitungsfrist . " => " . $daten->bearbeitungsfrist . "\n";
			if ($alter_eintrag->status != $daten->status) $aenderungen .= "Status: " . $alter_eintrag->status . " => " . $daten->status . "\n";
			if ($alter_eintrag->fristverlaengerung != $daten->fristverlaengerung) $aenderungen .= "Fristverlängerung: " . $alter_eintrag->fristverlaengerung . " => " . $daten->fristverlaengerung . "\n";
			if (isset($daten->initiatorInnen) && $alter_eintrag->initiatorInnen != $daten->initiatorInnen) $aenderungen .= "Initiatoren: " . $alter_eintrag->initiatorInnen . " => " . $daten->initiatorInnen . "\n";
			if ($alter_eintrag->gestellt_von != $daten->gestellt_von) $aenderungen .= "Gestellt von: " . $alter_eintrag->gestellt_von . " => " . $daten->gestellt_von . "\n";
			if ($alter_eintrag->antrags_nr != $daten->antrags_nr) $aenderungen .= "Vorlagen-Nr: " . $alter_eintrag->antrags_nr . " => " . $daten->antrags_nr . "\n";
			if ($alter_eintrag->ba_nr != $daten->ba_nr) $aenderungen .= "BA: " . $alter_eintrag->ba_nr . " => " . $daten->ba_nr . "\n";
			if (isset($daten->referat) && $alter_eintrag->referat != $daten->referat) $aenderungen .= "Referat: " . $alter_eintrag->referat . " => " . $daten->referat . "\n";
			if (isset($daten->referent) && $alter_eintrag->referent != $daten->referent) $aenderungen .= "Referent: " . $alter_eintrag->referent . " => " . $daten->referent . "\n";
			if ($aenderungen != "") $changed = true;
		}

		if ($changed) echo "Verändert: " . ($changed ? "Ja" : "Nein") . "\n";

		if ($changed) {
			if ($alter_eintrag) {
				$alter_eintrag->copyToHistory();
				$alter_eintrag->setAttributes($daten->getAttributes());
				if ($alter_eintrag->wahlperiode == "") $alter_eintrag->wahlperiode = "?";
				if (!$alter_eintrag->save()) {
					echo "Vorlage 1\n";
					var_dump($alter_eintrag->getErrors());
					die("Fehler");
				}
				$daten = $alter_eintrag;
			} else {
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
			$aenderungen .= AntragDokument::create_if_necessary(AntragDokument::$TYP_STADTRAT_VORLAGE, $daten, $dok);
		}

		foreach ($antrag_links as $link) {
			/** @var Antrag $antrag  */
			$antrag = Antrag::model()->findByPk(IntVal($link));
			if (!$antrag) {
				$parser = new StadtratsantragParser();
				$parser->parse($link);

				$antrag = Antrag::model()->findByPk(IntVal($link));
			}
			if (!$antrag) if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "Stadtratsvorlage - Zugordnungs Error", $vorlage_id . " - " . $link);

			$sql = Yii::app()->db->createCommand();
			$sql->select("antrag2")->from("antraege_vorlagen")->where("antrag1 = " . IntVal($vorlage_id) . " AND antrag2 = " . IntVal($antrag->id));
			$data = $sql->queryAll();
			if (count($data) == 0) {
				$daten->addAntrag($antrag);
				$aenderungen .= "Neuer Antrag zugeordnet: http://www.ris-muenchen.de/RII2/RII/ris_antrag_detail.jsp?risid=$link\n";
			}
		}

		var_dump($aenderungen);

		if ($aenderungen != "") {
			$aend = new RISAenderung();
			$aend->ris_id = $daten->id;
			$aend->ba_nr = $daten->ba_nr;
			$aend->typ = RISAenderung::$TYP_STADTRAT_VORLAGE;
			$aend->datum = new CDbExpression("NOW()");
			$aend->aenderungen = $aenderungen;
			$aend->save();
		}
	}

	public function parseSeite($seite) {
		echo "Seite: $seite\n";
		$text = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_vorlagen_trefferliste.jsp?txtSuchbegriff=&txtPosition=$seite");
		$txt = explode("<!-- ergebnisreihen -->", $text);
		$txt = explode("<div class=\"ergebnisfuss\">", $txt[1]);
		preg_match_all("/ris_vorlagen_detail\.jsp\?risid=([0-9]+)[\"'& ]/siU", $txt[0], $matches);
		for ($i = count($matches[1])-1; $i >= 0; $i--) $this->parse($matches[1][$i]);
		return $matches[1];
	}


	public function parseAlle() {
		$anz = 18880;
		for ($i = $anz; $i >= 0; $i -= 10) {
			echo ($anz - $i) . " / $anz\n";
			$this->parseSeite($i);
		}

	}

	public function parseUpdate() {
		$loaded_ids = array();
		for ($i = 400; $i >= 0; $i -= 10) {
			$ids = $this->parseSeite($i);
			$loaded_ids = array_merge($loaded_ids, array_map("IntVal", $ids));
		}

		$crit = new CDbCriteria();
		$crit->condition = "typ='" . addslashes(Antrag::$TYP_STADTRAT_VORLAGE) . "' AND status NOT IN ('Endgültiger Beschluss', 'abgeschlossen') AND gestellt_am > NOW() - INTERVAL 2 YEAR";
		if (count($loaded_ids) > 0) $crit->addNotInCondition("id", $loaded_ids);

		/** @var array|Antrag[] $antraege  */
		$antraege = Antrag::model()->findAll($crit);
		foreach ($antraege as $antrag) $this->parse($antrag->id);
	}


}