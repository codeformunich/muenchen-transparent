<?php

class StadtratsantragParser extends RISParser {

	public function parse($antrag_id) {
		$antrag_id = IntVal($antrag_id);

		echo "- Antrag $antrag_id\n";

		$html_details = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_antrag_detail.jsp?risid=" . $antrag_id);
		$html_dokumente = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_antrag_dokumente.jsp?risid=" . $antrag_id);
		//$html_ergebnisse = load_file("http://www.ris-muenchen.de/RII2/RII/ris_antrag_ergebnisse.jsp?risid=" . $antrag_id);

		if (trim($html_details) == "" || trim($html_dokumente) == "") return;

		$daten = new Antrag();
		$daten->id = $antrag_id;
		$daten->datum_letzte_aenderung = new CDbExpression('NOW()');
		$daten->typ = Antrag::$TYP_STADTRAT_ANTRAG;

		$dokumente = array();
		// $ergebnisse = array();

		$dat_details = explode("<!-- bereichsbild, bereichsheadline, allgemeiner text -->", $html_details);
		if (!isset($dat_details[1])) {
			echo $antrag_id . " - " . "http://www.ris-muenchen.de/RII2/RII/ris_antrag_detail.jsp?risid=" . $antrag_id  . "\n";
			var_dump($dat_details);
			return;
		}
		$dat_details = explode("<!-- detailbereich -->", $dat_details[1]);

		preg_match("/<h3.*>.*&nbsp;(.*)<\/h3/siU", $dat_details[0], $matches);
		if (count($matches) == 2) $daten->antrags_nr = $matches[1];

		preg_match_all("/class=\"detail_row\">.*detail_label\">(.*)<\/d.*detail_div\">(.*)<\/div/siU", $dat_details[0], $matches);
		for ($i = 0; $i < count($matches[1]); $i++) switch ($matches[1][$i]) {
			case "Betreff:": $daten->betreff = $this->text_simple_clean($matches[2][$i]); break;
			case "Status:": $daten->status = $this->text_simple_clean($matches[2][$i]); break;
			case "Bearbeitung:": $daten->bearbeitung = trim(strip_tags($matches[2][$i])); break;
		}

		$dat_details = explode("<!-- details und tabelle -->", $html_details);
		$dat_details = explode("<!-- tabellenfuss -->", $dat_details[1]);

		preg_match_all("/<span class=\"itext\">(.*)<\/span.*detail_div_(left|right|left_long)\">(.*)<\/div/siU", $dat_details[0], $matches);
		for ($i = 0; $i < count($matches[1]); $i++) if ($matches[3][$i] != "&nbsp;") switch ($matches[1][$i]) {
			case "Typ:": $daten->antrag_typ = $matches[3][$i]; break;
			case "Zust&auml;ndiges Referat:": $daten->referat = $matches[3][$i]; break;
			case "Gestellt am:": $daten->gestellt_am = $this->date_de2mysql($matches[3][$i]); break;
			case "Wahlperiode:": $daten->wahlperiode = $matches[3][$i]; break;
			case "Bearbeitungsfrist:": $daten->bearbeitungsfrist = $this->date_de2mysql($matches[3][$i]); break;
			case "Fristverl&auml;ngerung:": $daten->fristverlaengerung = $this->date_de2mysql($matches[3][$i]); break;
			case "Gestellt von:": $daten->gestellt_von = $matches[3][$i]; break;
			case "Initiatoren:": if ($matches[3][$i] != "&nbsp;") $daten->initiatorInnen = $matches[3][$i]; break;
		}

		preg_match_all("/<li><span class=\"iconcontainer\">.*href=\"(.*)\">(.*)<\/a>/siU", $html_dokumente, $matches);
		for ($i = 0; $i < count($matches[1]); $i++) {
			$dokumente[] = array(
				"url" => $matches[1][$i],
				"name" => $matches[2][$i],
			);
		}
		/*
		$dat_ergebnisse = explode("<!-- tabellenkopf -->", $html_ergebnisse);
		$dat_ergebnisse = explode("<!-- tabellenfuss -->", $dat_ergebnisse[1]);
		preg_match_all("<tr>.*bghell  tdborder\"><a.*\">(.*)<\/a>.*
		http://www.ris-muenchen.de/RII2/RII/ris_antrag_ergebnisse.jsp?risid=6127
		*/

		$aenderungen = "";

		/** @var Antrag $alter_eintrag */
		$alter_eintrag = Antrag::model()->findByPk($antrag_id);
		$changed = true;
		if ($alter_eintrag) {
			$changed = false;
			if ($alter_eintrag->bearbeitungsfrist != $daten->bearbeitungsfrist) $aenderungen .= "Bearbeitungsfrist: " . $alter_eintrag->bearbeitungsfrist . " => " . $daten->bearbeitungsfrist . "\n";
			if ($alter_eintrag->status != $daten->status) $aenderungen .= "Status: " . $alter_eintrag->status . " => " . $daten->status . "\n";
			if ($alter_eintrag->fristverlaengerung != $daten->fristverlaengerung) $aenderungen .= "Fristverlängerung: " . $alter_eintrag->fristverlaengerung . " => " . $daten->fristverlaengerung . "\n";
			if (isset($daten->initiatorInnen) && $alter_eintrag->initiatorInnen != $daten->initiatorInnen) $aenderungen .= "Initiatoren: " . $alter_eintrag->initiatorInnen . " => " . $daten->initiatorInnen . "\n";
			if ($alter_eintrag->gestellt_von != $daten->gestellt_von) $aenderungen .= "Gestellt von: " . $alter_eintrag->gestellt_von . " => " . $daten->gestellt_von . "\n";
			if ($alter_eintrag->antrags_nr != $daten->antrags_nr) $aenderungen .= "Antrags-Nr: " . $alter_eintrag->antrags_nr . " => " . $daten->antrags_nr . "\n";
			if ($aenderungen != "") $changed = true;
		}

		if ($aenderungen != "") echo "Verändert: " . $aenderungen . "\n";

		if ($changed) {
			if ($aenderungen == "") $aenderungen = "Neu angelegt\n";
			
			if ($alter_eintrag) {
				$alter_eintrag->copyToHistory();
				$alter_eintrag->setAttributes($daten->getAttributes(), false);
				if (!$alter_eintrag->save()) {
					echo "StadtratAntrag 1\n";
					var_dump($alter_eintrag->getErrors());
					die("Fehler");
				}
				$daten = $alter_eintrag;
			} else {
				if (!$daten->save()) {
					echo "StadtratAntrag 2\n";
					var_dump($daten->getErrors());
					die("Fehler");
				}
			}

			$daten->resetPersonen();
		}

		foreach ($dokumente as $dok) {
			$aenderungen .= AntragDokument::create_if_necessary(AntragDokument::$TYP_STADTRAT_ANTRAG, $daten, $dok);
		}

		if ($aenderungen != "") {
			$aend = new RISAenderung();
			$aend->ris_id = $daten->id;
			$aend->ba_nr = $daten->ba_nr;
			$aend->typ = RISAenderung::$TYP_STADTRAT_ANTRAG;
			$aend->datum = new CDbExpression("NOW()");
			$aend->aenderungen = $aenderungen;
			$aend->save();
		}
	}



	public function parseSeite($seite) {
		$text = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_antrag_trefferliste.jsp?txtPosition=$seite");
		$txt = explode("<!-- ergebnisreihen -->", $text);
		if (!isset($txt[1])) {
			echo "- nichts\n";
			return array();
		}
		$txt = explode("<div class=\"ergebnisfuss\">", $txt[1]);
		preg_match_all("/ris_antrag_detail.jsp\?risid=([0-9]+)[\"'& ]/siU", $txt[0], $matches);
		for ($i = count($matches[1])-1; $i >= 0; $i--) $this->parse($matches[1][$i]);
		return $matches[1];
	}

	public function parseAlle() {
		$anz = 13000;
		for ($i = $anz; $i >= 0; $i -= 10) {
			echo ($anz - $i) . " / $anz\n";
			$this->parseSeite($i);
		}
	}

	public function parseUpdate() {
		$loaded_ids = array();

		for ($i = 200; $i >= 0; $i -= 10) {
			$ids = $this->parseSeite($i);
			$loaded_ids = array_merge($loaded_ids, array_map("IntVal", $ids));
		}

		$crit = new CDbCriteria();
		$crit->condition = "typ='" . addslashes(Antrag::$TYP_STADTRAT_ANTRAG) . "' AND status != 'erledigt' AND gestellt_am > NOW() - INTERVAL 2 YEAR AND ((TO_DAYS(bearbeitungsfrist)-TO_DAYS(CURRENT_DATE()) < 14 AND TO_DAYS(bearbeitungsfrist)-TO_DAYS(CURRENT_DATE()) > -14) OR ((TO_DAYS(CURRENT_DATE()) - TO_DAYS(gestellt_am)) % 3) = 0)";
		if (count($loaded_ids) > 0) $crit->addNotInCondition("id", $loaded_ids);

		/** @var array|Antrag[] $antraege  */
		$antraege = Antrag::model()->findAll($crit);
		foreach ($antraege as $antrag) $this->parse($antrag->id);
	}
}