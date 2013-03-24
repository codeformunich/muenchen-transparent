<?php

class BAInitiativeParser extends RISParser {

	public function parse($antrag_id) {
		$antrag_id = IntVal($antrag_id);

		echo "- Antrag $antrag_id\n";

		$html_details = RISTools::load_file("http://www.ris-muenchen.de/RII2/BA-RII/ba_initiativen_details.jsp?Id=$antrag_id");
		$html_dokumente = RISTools::load_file("http://www.ris-muenchen.de/RII2/BA-RII/ba_initiativen_dokumente.jsp?Id=$antrag_id");
		//$html_ergebnisse = load_file("http://www.ris-muenchen.de/RII2/RII/ris_antrag_ergebnisse.jsp?risid=" . $antrag_id);

		$daten = new Antrag();
		$daten->id = $antrag_id;
		$daten->datum_letzte_aenderung = new CDbExpression('NOW()');
		$daten->typ = Antrag::$TYP_BA_INITIATIVE;

		$dokumente = array();
		//$ergebnisse = array();

		preg_match("/<h3.*>.* +(.*)<\/h3/siU", $html_details, $matches);
		if (count($matches) == 2) $daten->antrags_nr = trim($matches[1]);

		$dat_details = explode("<h3 class=\"introheadline\">BA-Initiativen-Nummer", $html_details);
		$dat_details = explode("<div class=\"formularcontainer\">", $dat_details[1]);
		preg_match_all("/class=\"detail_row\">.*detail_label\">(.*)<\/d.*detail_div\">(.*)<\/div/siU", $dat_details[0], $matches);

		for ($i = 0; $i < count($matches[1]); $i++) switch (trim($matches[1][$i])) {
			case "Betreff:": $daten->betreff = $this->text_simple_clean($matches[2][$i]); break;
			case "Status:": $daten->status = $this->text_simple_clean($matches[2][$i]); break;
			case "Bearbeitung:": $daten->bearbeitung = trim(strip_tags($matches[2][$i])); break;
		}

		$dat_details = explode("<div class=\"detailborder\">", $html_details);
		$dat_details = explode("<!-- seitenfuss -->", $dat_details[1]);

		preg_match_all("/<span class=\"itext\">(.*)<\/span.*detail_div_(left|right|left_long)\">(.*)<\/div/siU", $dat_details[0], $matches);
		for ($i = 0; $i < count($matches[1]); $i++) if ($matches[3][$i] != "&nbsp;") switch ($matches[1][$i]) {
			case "Zust&auml;ndiges Referat:": $daten->referat = $matches[3][$i]; break;
			case "Gestellt am:": $daten->gestellt_am = $this->date_de2mysql($matches[3][$i]); break;
			case "Wahlperiode:": $daten->wahlperiode = $matches[3][$i]; break;
			case "Bearbeitungsfrist:": $daten->bearbeitungsfrist = $this->date_de2mysql($matches[3][$i]); break;
			case "Registriert am:": $daten->registriert_am = $this->date_de2mysql($matches[3][$i]); break;
			case "Bezirksausschuss:": $daten->ba_nr = IntVal($matches[3][$i]); break;
			case "Typ:": $daten->antrag_typ = strip_tags($matches[3][$i]); break;
			case "TO aufgenommen am:": $daten->initiative_to_aufgenommen = $this->date_de2mysql($matches[3][$i]); break;
		}
		if ($daten->wahlperiode == "") $daten->wahlperiode = "?";

		preg_match_all("/<li><span class=\"iconcontainer\">.*href=\"(.*)\".*>(.*)<\/a>/siU", $html_dokumente, $matches);
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

		if ($daten->ba_nr == 0) {
			echo "Keine BA-Angabe";
			$GLOBALS["RIS_PARSE_ERROR_LOG"][] = "Keine BA-Angabe (Initiative): $antrag_id";
			return;
		}

		$aenderungen = "";

		/** @var Antrag $alter_eintrag */
		$alter_eintrag = Antrag::model()->findByPk($antrag_id);
		$changed = true;
		if ($alter_eintrag) {
			$changed = false;
			if ($alter_eintrag->bearbeitungsfrist != $daten->bearbeitungsfrist) $aenderungen .= "Bearbeitungsfrist: " . $alter_eintrag->bearbeitungsfrist . " => " . $daten->bearbeitungsfrist . "\n";
			if ($alter_eintrag->status != $daten->status) $aenderungen .= "Status: " . $alter_eintrag->status . " => " . $daten->status . "\n";
			if ($alter_eintrag->fristverlaengerung != $daten->fristverlaengerung) $aenderungen .= "Fristverlängerung: " . $alter_eintrag->fristverlaengerung . " => " . $daten->fristverlaengerung . "\n";
			if ($alter_eintrag->initiative_to_aufgenommen != $daten->initiative_to_aufgenommen) $aenderungen .= "In TO Aufgenommen: " . $alter_eintrag->initiative_to_aufgenommen . " => " . $daten->initiative_to_aufgenommen . "\n";
			if ($aenderungen != "") $changed = true;
			if ($alter_eintrag->wahlperiode == "") $alter_eintrag->wahlperiode = "?";
		}

		echo "Verändert: " . ($changed ? "Ja" : "Nein") . "\n";

		if ($changed) {
			if ($aenderungen == "") $aenderungen = "Neu angelegt\n";

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
			$aenderungen .= AntragDokument::create_if_necessary(AntragDokument::$TYP_BA_INITIATIVE, $daten, $dok);
		}

		if ($aenderungen != "") {
			$aend = new RISAenderung();
			$aend->ris_id = $daten->id;
			$aend->ba_nr = $daten->ba_nr;
			$aend->typ = RISAenderung::$TYP_BA_INITIATIVE;
			$aend->datum = new CDbExpression("NOW()");
			$aend->aenderungen = $aenderungen;
			$aend->save();
		}
	}

	public function parseSeite($seite) {
		$text = RISTools::load_file("http://www.ris-muenchen.de/RII2/BA-RII/ba_initiativen.jsp?Trf=n&Start=$seite");

		$txt = explode("<!-- tabellenkopf -->", $text);
		$txt = explode("<div class=\"ergebnisfuss\">", $txt[1]);
		preg_match_all("/ba_initiativen_details\.jsp\?Id=([0-9]+)[\"'& ]/siU", $txt[0], $matches);
		for ($i = count($matches[1])-1; $i >= 0; $i--) $this->parse($matches[1][$i]);
		return $matches[1];
	}

	public function parseAlle() {
		$anz = 3300;
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
	}


}