<?php

class StadtraetInnenParser extends RISParser {

	private $bearbeitete_stadtraete = array();
	private $antraege_alle = false;

	/**
	 * @param bool $set
	 */
	public function setParseAlleAntraege($set) {
		$this->antraege_alle = $set;
	}

	public function parse_antraege($stadtraetIn_id, $seite) {
		$antr_text = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_antrag_trefferliste.jsp?nav=2&selWahlperiode=0&steller=$stadtraetIn_id&txtPosition=" . ($seite * 10));

		preg_match_all("/ris_antrag_detail\.jsp\?risid=(?<antrag_id>[0-9]+)[\"'& ]/siU", $antr_text, $matches);
		foreach($matches["antrag_id"] as $antrag_id) 		try {
			Yii::app()->db->createCommand()->insert("antraege_stadtraetInnen", array("antrag_id" => $antrag_id, "stadtraetIn_id" => $stadtraetIn_id, "gefunden_am" => new CDbExpression("NOW()")));
		} catch (Exception $e) {
		}
	}

	public function parse($stadtraetIn_id) {

		$stadtraetIn_id = IntVal($stadtraetIn_id);

		echo "- StadträtIn $stadtraetIn_id\n";

		$html_details = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_mitglieder_detail_fraktion.jsp?risid=$stadtraetIn_id");

		$daten = new StadtraetIn();
		$daten->id = $stadtraetIn_id;
		$daten->web = "";

		if (preg_match("/introheadline\">(.*)( ?\([^\)]*\) ?)<\/h3/siU", $html_details, $matches)) {
			$daten->name = trim(str_replace("&nbsp;", " ", $matches[1]));
		}

		if (preg_match("/Gew&auml;hlt am:.*detail_div\">([0-9\.]+)<\/div/siU", $html_details, $matches)) {
			$x = explode(".", $matches[1]);
			$daten->gewaehlt_am = $x[2]."-".$x[1]."-".$x[0];
		}

		if (preg_match("/Lebenslauf.*detail_div\">(.*)<\/di/siU", $html_details, $matches)) {
			$daten->bio = str_replace("<br />", "", $matches[1]);
		}

		$aenderungen = "";

		/** @var StadtraetIn $alter_eintrag */
		$alter_eintrag = StadtraetIn::model()->findByPk($stadtraetIn_id);
		$changed = true;
		if ($alter_eintrag) {
			$changed = false;
			if ($alter_eintrag->name != $daten->name) $aenderungen .= "Name: " . $alter_eintrag->name . " => " . $daten->name . "\n";
			if ($alter_eintrag->gewaehlt_am != $daten->gewaehlt_am) $aenderungen .= "Gewählt am: " . $alter_eintrag->gewaehlt_am . " => " . $daten->gewaehlt_am . "\n";
			if ($alter_eintrag->bio != $daten->bio) $aenderungen .= "Biografie: " . $alter_eintrag->bio . " => " . $daten->bio . "\n";
			if ($aenderungen != "") $changed = true;
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


		$unten = explode("Tabellarische &Uuml;bersicht der Zugeh&ouml;rigkei", $html_details);
		$unten = $unten[1];

		preg_match_all("/ris_fraktionen_detail\.jsp\?risid=(?<fraktion_id>[0-9]+)&amp;periodeid=(?<wahlperiode>[0-9]+)[\"'& ].*tdborder\">(?<mitgliedschaft>[^<]*)<\/td>.*Funktion[^>]*>(?<funktion>[^<]*) *<.*<\/tr/siU", $unten, $matches);
		for ($i = 0; $i < count($matches[1]); $i++) {
			$str_fraktion = new StadtraetInFraktion();
			$str_fraktion->fraktion_id = $matches["fraktion_id"][$i];
			$str_fraktion->stadtraetIn_id = $stadtraetIn_id;
			$str_fraktion->wahlperiode = $matches["wahlperiode"][$i];
			$str_fraktion->funktion = $matches["funktion"][$i];
			$str_fraktion->mitgliedschaft = $matches["mitgliedschaft"][$i];

			/** @var array|StadtraetInFraktion[] $bisherige_fraktionen  */
			$bisherige_fraktionen = StadtraetInFraktion::model()->findAllByAttributes(array("stadtraetIn_id" => $stadtraetIn_id));
			/** @var null|StadtraetInFraktion $bisherige  */
			$bisherige = null;
			foreach ($bisherige_fraktionen as $fr)  {
				if ($fr->fraktion_id == $matches["fraktion_id"][$i] && $fr->wahlperiode == $matches["wahlperiode"][$i]) $bisherige = $fr;
			}
			if ($bisherige === null) {
				$str_fraktion->save();
				$aenderungen = "Neue Fraktionszugehörigkeit: " . $str_fraktion->fraktion->name . "\n";
			} else {
				if ($bisherige->wahlperiode != $matches["wahlperiode"][$i]) $aenderungen .= "Neue Wahlperiode: " . $bisherige->wahlperiode . " => " . $matches["wahlperiode"][$i] . "\n";
				if ($bisherige->funktion != $matches["funktion"][$i]) $aenderungen .= "Neue Funktion in der Fraktion: " . $bisherige->funktion . " => " . $matches["funktion"][$i] . "\n";
				if ($bisherige->mitgliedschaft != $matches["mitgliedschaft"][$i]) $aenderungen .= "Mitgliedschaft in der Fraktion: " . $bisherige->mitgliedschaft . " => " . $matches["mitgliedschaft"][$i] . "\n";
				$bisherige->setAttributes($str_fraktion->getAttributes());
				$bisherige->save();
			}
		}


		if ($aenderungen != "") echo "Verändert: " . $aenderungen . "\n";

		if ($aenderungen != "") {
			$aend = new RISAenderung();
			$aend->ris_id = $daten->id;
			$aend->ba_nr = null;
			$aend->typ = RISAenderung::$TYP_STADTRAETIN;
			$aend->datum = new CDbExpression("NOW()");
			$aend->aenderungen = $aenderungen;
			$aend->save();
		}

		if ($this->antraege_alle) {
			$text = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_antrag_trefferliste.jsp?nav=2&selWahlperiode=0&steller=$stadtraetIn_id&txtPosition=0");
			if (preg_match("/Suchergebnisse:.* ([0-9]+)<\/p>/siU", $text, $matches)) {
				$seiten = Ceil($matches[1] / 10);
				for ($i = 0; $i < $seiten; $i++) $this->parse_antraege($stadtraetIn_id, $i);
			} else echo "Keine Anträge gefunden\n";
		} else for ($i = 0; $i < 2; $i++) {
			$this->parse_antraege($stadtraetIn_id, $i);
		}
	}



	public function parseSeite($seite) {
		$text = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_mitglieder_trefferliste.jsp?txtPosition=$seite");
		$txt = explode("<!-- tabellenkopf -->", $text);
		if (!isset($txt[1])) {
			echo "- leer\n";
			return array();
		}
		$txt = explode("<div class=\"ergebnisfuss\">", $txt[1]);
		preg_match_all("/ris_mitglieder_detail\.jsp\?risid=([0-9]+)[\"'& ]/siU", $txt[0], $matches);
		for ($i = count($matches[1])-1; $i >= 0; $i--) if (!in_array($matches[1][$i], $this->bearbeitete_stadtraete)) {
			$this->parse($matches[1][$i]);
			$this->bearbeitete_stadtraete[] = $matches[1][$i];
		}
		return $matches[1];
	}


	public function parseAlle() {
		$anz = 300;
		$this->bearbeitete_stadtraete = array();
		for ($i = $anz; $i >= 0; $i -= 10) {
			echo ($anz - $i) . " / $anz\n";
			$this->parseSeite($i);
		}
	}

	public function parseUpdate() {
		$this->parseAlle();
	}
}