<?php
class BAGremienParser extends RISParser {

	public function parse($gremien_id)
	{
		$gremien_id = IntVal($gremien_id);
		if (RATSINFORMANT_CALL_MODE != "cron") echo "- Gremium $gremien_id\n";

		$html_details = RISTools::load_file("http://www.ris-muenchen.de/RII2/BA-RII/ba_gremien_details.jsp?Id=" . $gremien_id);

		$daten                         = new Gremium();
		$daten->id                     = $gremien_id;
		$daten->datum_letzte_aenderung = new CDbExpression('NOW()');

		if (preg_match("/introheadline\">([^>]+)<\/h3/siU", $html_details, $matches)) $daten->name = $matches[1];
		if (preg_match("/introheadline\">(?:BA|UA) ([0-9]+) /siU", $html_details, $matches)) $daten->ba_nr = $matches[1];
		if (preg_match("/rzel:.*detail_div\">([^>]*)<\//siU", $html_details, $matches)) $daten->kuerzel = $matches[1];
		if (preg_match("/Gremiumtyp:.*detail_div\">([^>]*)<\//siU", $html_details, $matches)) $daten->gremientyp = $matches[1];

		$aenderungen = "";

		foreach ($daten as $key => $val) $daten[$key] = ($val === null ? null : html_entity_decode(trim($val), ENT_COMPAT, "UTF-8"));

		/** @var Gremium $alter_eintrag */
		$alter_eintrag = Gremium::model()->findByPk($gremien_id);
		$changed       = true;
		if ($alter_eintrag) {
			$changed = false;
			if ($alter_eintrag->name != $daten->name) $aenderungen .= "Name: " . $alter_eintrag->name . " => " . $daten->name . "\n";
			if ($alter_eintrag->ba_nr != $daten->ba_nr) $aenderungen .= "BA: " . $alter_eintrag->ba_nr . " => " . $daten->ba_nr . "\n";
			if ($alter_eintrag->kuerzel != $daten->kuerzel) $aenderungen .= "Kürzel: " . $alter_eintrag->kuerzel . " => " . $daten->kuerzel . "\n";
			if ($alter_eintrag->gremientyp != $daten->gremientyp) $aenderungen .= "Gremientyp: " . $alter_eintrag->gremientyp . " => " . $daten->gremientyp . "\n";
			if ($aenderungen != "") $changed = true;
		}

		if ($changed) {
			if ($alter_eintrag) {
				$alter_eintrag->copyToHistory();
				$alter_eintrag->setAttributes($daten->getAttributes());
				if (!$alter_eintrag->save()) {
					echo "Gremium 3";
					var_dump($alter_eintrag->getErrors());
					die("Fehler");
				}
				$daten = $alter_eintrag;
			} else {
				if (!$daten->save()) {
					echo "Gremium 4";
					var_dump($daten->getErrors());
					die("Fehler");
				}
			}

			$aend              = new RISAenderung();
			$aend->ris_id      = $daten->id;
			$aend->ba_nr       = null;
			$aend->typ         = RISAenderung::$TYP_BA_GREMIUM;
			$aend->datum       = new CDbExpression("NOW()");
			$aend->aenderungen = $aenderungen;
			$aend->save();
		}
	}

	public function parseSeite($seite, $first)
	{
		if (RATSINFORMANT_CALL_MODE != "cron") echo "BA-Anträge Seite $seite\n";
		$text = RISTools::load_file("http://www.ris-muenchen.de/RII2/BA-RII/ba_gremien.jsp?Start=$seite");

		$txt = explode("<!-- tabellenkopf -->", $text);
		$txt = explode("<div class=\"ergebnisfuss\">", $txt[1]);
		preg_match_all("/ba_gremien_details\.jsp\?Id=([0-9]+)[\"'& ]/siU", $txt[0], $matches);
		if ($first && count($matches[1]) > 0) mail(Yii::app()->params['adminEmail'], "BA-Gremien VOLL", "Erste Seite voll: $seite");
		for ($i = count($matches[1])-1; $i >= 0; $i--) $this->parse($matches[1][$i]);
		return $matches[1];
	}

	public function parseAlle()
	{
		$anz = 190;
		$first = true;
		for ($i = $anz; $i >= 0; $i -= 10) {
			if (RATSINFORMANT_CALL_MODE != "cron") echo ($anz - $i) . " / $anz\n";
			$this->parseSeite($i, $first);
			$first = false;
		}
	}

	public function parseUpdate()
	{
		$this->parseAlle();
	}
}

