<?php

class BATerminParser extends RISParser
{


	public function parse($termin_id)
	{
		$termin_id = IntVal($termin_id);
		if (RATSINFORMANT_CALL_MODE != "cron") echo "- Termin $termin_id\n";

		$html_details   = RISTools::load_file("http://www.ris-muenchen.de/RII2/BA-RII/ba_sitzungen_details.jsp?Id=$termin_id");
		$html_dokumente = RISTools::load_file("http://www.ris-muenchen.de/RII2/BA-RII/ba_sitzungen_dokumente.jsp?Id=$termin_id");
		$html_to        = RISTools::load_file("http://www.ris-muenchen.de/RII2/BA-RII/ba_sitzungen_tagesordnung.jsp?Id=$termin_id");

		$daten                         = new Termin();
		$daten->id                     = $termin_id;
		$daten->datum_letzte_aenderung = new CDbExpression('NOW()');
		$daten->gremium_id             = NULL;

		$dokumente = array();

		if (preg_match("/ba_gremien_details\.jsp\?Id=([0-9]+)[\"'& ]/siU", $html_details, $matches)) $daten->gremium_id = IntVal($matches[1]);
		if ($daten->gremium_id) {
			$gr = Gremium::model()->findByPk($daten->gremium_id);
			if (!$gr) {
				echo "Lege Gremium an: " . $daten->gremium_id . "\n";
				Gremium::parse_ba_gremien($daten->gremium_id);
			}
		}

		if (preg_match("/Termin:.*detail_div\">([^&<]+)[&<]/siU", $html_details, $matches)) {
			$termin = $matches[1];
			$MONATE = array(
				"januar"    => "01",
				"februar"   => "02",
				"märz"      => "03",
				"april"     => "04",
				"mai"       => "05",
				"juni"      => "06",
				"juli"      => "07",
				"august"    => "08",
				"september" => "09",
				"oktober"   => "10",
				"november"  => "11",
				"dezember"  => "12",
			);
			$x      = explode(" ", trim($termin));
			$tag    = IntVal($x[1]);
			if ($tag < 10) $tag = "0" . IntVal($tag);
			$jahr  = IntVal($x[2]);
			$y     = explode(".", $x[1]);
			$monat = $MONATE[mb_strtolower($y[1])];
			if ($monat < 10) $monat = "0" . IntVal($monat);
			$zeit          = $x[3];
			$daten->termin = "${jahr}-${monat}-${tag} ${zeit}:00";
		}

		if (preg_match("/Sitzungsort:.*detail_div\">([^<]*)[<]/siU", $html_details, $matches)) $daten->sitzungsort = $matches[1];
		if (preg_match("/Bezirksausschuss:.*link_bold_noimg\">([0-9]+)[\"'& ]/siU", $html_details, $matches)) $daten->ba_nr = IntVal($matches[1]);
		if (preg_match("/chste Sitzung:.*ba_sitzungen_details\.jsp\?Id=([0-9]+)[\"'& ]/siU", $html_details, $matches)) $daten->termin_next_id = $matches[1];
		if (preg_match("/Letzte Sitzung:.*ba_sitzungen_details\.jsp\?Id=([0-9]+)[\"'& ]/siU", $html_details, $matches)) $daten->termin_prev_id = $matches[1];
		if (preg_match("/Wahlperiode:.*detail_div_left_long\">([^>]*)<\//siU", $html_details, $matches)) $daten->wahlperiode = $matches[1];
		if (preg_match("/Status:.*detail_div_left_long\">([^>]*)<\//siU", $html_details, $matches)) $daten->status = $matches[1];
		if (trim($daten->wahlperiode) == "") $daten->wahlperiode = "?";

		preg_match_all("/<li><span class=\"iconcontainer\">.*href=\"(.*)\".*>(.*)<\/a>/siU", $html_dokumente, $matches);
		for ($i = 0; $i < count($matches[1]); $i++) {
			$dokumente[] = array(
				"url"  => $matches[1][$i],
				"name" => $matches[2][$i],
			);
		}

		$aenderungen = "";

		/** @var Termin $alter_eintrag */
		$alter_eintrag = Termin::model()->findByPk($termin_id);
		$changed       = true;
		if ($alter_eintrag) {
			$changed = false;
			if ($alter_eintrag->termin != $daten->termin) $aenderungen .= "Termin: " . $alter_eintrag->termin . " => " . $daten->termin . "\n";
			if ($alter_eintrag->gremium_id != $daten->gremium_id) $aenderungen .= "Gremium-ID: " . $alter_eintrag->gremium_id . " => " . $daten->gremium_id . "\n";
			if ($alter_eintrag->sitzungsort != $daten->sitzungsort) $aenderungen .= "Sitzungsort: " . $alter_eintrag->sitzungsort . " => " . $daten->sitzungsort . "\n";
			if ($alter_eintrag->ba_nr != $daten->ba_nr) $aenderungen .= "BA-Nr.: " . $alter_eintrag->ba_nr . " => " . $daten->ba_nr . "\n";
			if ($alter_eintrag->termin_next_id != $daten->termin_next_id) $aenderungen .= "Nächster Termin: " . $alter_eintrag->termin_next_id . " => " . $daten->termin_next_id . "\n";
			if ($alter_eintrag->termin_prev_id != $daten->termin_prev_id) $aenderungen .= "Voriger Termin: " . $alter_eintrag->termin_prev_id . " => " . $daten->termin_prev_id . "\n";
			if ($alter_eintrag->wahlperiode != $daten->wahlperiode) $aenderungen .= "Wahlperiode: " . $alter_eintrag->wahlperiode . " => " . $daten->wahlperiode . "\n";
			if ($alter_eintrag->status != $daten->status) $aenderungen .= "Status: " . $alter_eintrag->status . " => " . $daten->status . "\n";
			if ($aenderungen != "") $changed = true;
		}


		$match_top          = "<strong>(?<top>[0-9]+)\.<\/strong>"; // Hört mit "." auf, im Ggs. zu Zwischen-Überschriften
		$match_betreff      = "<td [^>]*topbetreff[^>]*>(?<betreff>.*)<\/td>";
		$match_vorlage      = "<td [^>]*vorgang[^>]*>(?<vorlage_holder>.*)<\/td>";
		$match_entscheidung = "<td [^>]*beschluss[^>]*>(?<entscheidung>.*)<\/td>";
		preg_match_all("/<tr class=\"ergebnistab_tr\">.*${match_top}.*${match_betreff}.*${match_vorlage}.*${match_entscheidung}.*<\/tr>/siU", $html_to, $matches);

		$nth_1_top = 0;

		for ($i = 0; $i < count($matches[0]); $i++) {
			if ($matches["top"][$i] == 1) $nth_1_top++;

			$betreff = static::text_clean_spaces($matches["betreff"][$i]);

			$vorlage_holder = trim(str_replace("&nbsp;", " ", $matches["vorlage_holder"][$i]));

			preg_match_all("/risid%3D(?<risid>[0-9]+)%27/siU", $vorlage_holder, $matches2);
			$vorlage_id = (isset($matches2["risid"][0]) ? $matches2["risid"][0] : null);
			preg_match_all("/ba_antraege_details\.jsp\?Id=(?<risid>[0-9]+)\"/siU", $vorlage_holder, $matches2);
			$baantrag_id = (isset($matches2["risid"][0]) ? $matches2["risid"][0] : null);

			if ($vorlage_id || $baantrag_id) {
				if ($vorlage_id) {
					$vorlage = Antrag::model()->findByPk($vorlage_id);
					if (!$vorlage) {
						echo "Creating: $vorlage_id\n";
						$p = new StadtratsvorlageParser();
						$p->parse($vorlage_id);
					}
				}
				if ($vorlage_id) {
					$vorlage = Antrag::model()->findByPk($vorlage_id);
					if (!$vorlage) {
						echo "Creating: $vorlage_id\n";
						$p = new StadtratsvorlageParser();
						$p->parse($vorlage_id);
					}
				}
			}

			/** @var AntragErgebnis $ergebnis */
			if ($vorlage_id) {
				$ergebnis = AntragErgebnis::model()->findByAttributes(array("sitzungstermin_id" => $termin_id, "antrag_id" => $vorlage_id));
				if (is_null($ergebnis)) $ergebnis = new AntragErgebnis();
				$ergebnis->antrag_id              = $vorlage_id;
			} elseif ($baantrag_id) {
				$ergebnis = AntragErgebnis::model()->findByAttributes(array("sitzungstermin_id" => $termin_id, "antrag_id" => $baantrag_id));
				if (is_null($ergebnis)) $ergebnis = new AntragErgebnis();
				$ergebnis->antrag_id              = $baantrag_id;
			} else {
				$ergebnis = AntragErgebnis::model()->findByAttributes(array("sitzungstermin_id" => $termin_id, "top_betreff" => $betreff));
				if (is_null($ergebnis)) $ergebnis = new AntragErgebnis();
				$ergebnis->antrag_id              = null;
			}

			$entscheidung_original = trim(str_replace("&nbsp;", " ", $matches["entscheidung"][$i]));
			$entscheidung          = trim(preg_replace("/<a[^>]*>[^<]*<\/a>/siU", "", $entscheidung_original));

			$ergebnis->datum_letzte_aenderung = new CDbExpression("NOW()");
			$ergebnis->sitzungstermin_id      = $termin_id;
			$ergebnis->sitzungstermin_datum   = $daten->termin;
			$ergebnis->top_nr                 = $nth_1_top . "-" . $matches["top"][$i];
			if ($ergebnis->entscheidung != $entscheidung) {
				$aenderungen .= "Entscheidung: " . $ergebnis->entscheidung . " => " . $entscheidung . "\n";
				$ergebnis->entscheidung = $entscheidung;
			}
			$ergebnis->top_betreff  = $betreff;
			$ergebnis->gremium_id   = $daten->gremium_id;
			$ergebnis->gremium_name = $daten->gremium->name;

			/*
			if (!is_null($vorlage_id)) {
				$html_vorlage_ergebnis = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_vorlagen_ergebnisse.jsp?risid=$vorlage_id");
				preg_match_all("/ris_sitzung_to.jsp\?risid=" . $termin_id . ".*<\/td>.*<\/td>.*tdborder\">(?<beschluss>.*)<\/td>/siU", $html_vorlage_ergebnis, $matches3);
				$beschluss = static::text_clean_spaces($matches3["beschluss"][0]);
				if ($ergebnis->beschluss_text != $beschluss) {
					$aenderungen .= "Beschluss: " . $ergebnis->beschluss_text . " => " . $beschluss . "\n";
					$ergebnis->beschluss_text = $beschluss;
				}
			}
			*/

			$ergebnis->save();

			preg_match("/<a title=\"(?<title>[^\"]*)\" [^>]*href=\"(?<url>[^ ]+)\"/siU", $entscheidung_original, $matches2);
			if (isset($matches2["url"]) && $matches2["url"] != "" && $matches2["url"] != "/RII2/RII/DOK/TOP/") {
				$aenderungen .= AntragDokument::create_if_necessary(AntragDokument::$TYP_BA_BESCHLUSS, $ergebnis, array("url" => $matches2["url"], "name" => $matches2["title"]));
			}
		}


		if ($changed) {
			if ($aenderungen == "") $aenderungen = "Neu angelegt\n";

			echo "BA-Termin $termin_id: Verändert: " . $aenderungen . "\n";

			if ($alter_eintrag) {
				$alter_eintrag->copyToHistory();
				$alter_eintrag->setAttributes($daten->getAttributes());
				if (!$alter_eintrag->save()) {
					if (RATSINFORMANT_CALL_MODE != "cron") echo "BA-Termin 1\n";
					var_dump($alter_eintrag->getErrors());
					die("Fehler");
				}
				$daten = $alter_eintrag;
			} else {
				if (!$daten->save()) {
					if (RATSINFORMANT_CALL_MODE != "cron") echo "BA-Termin 2\n";
					var_dump($daten->getErrors());
					die("Fehler");
				}
			}

			$aend              = new RISAenderung();
			$aend->ris_id      = $daten->id;
			$aend->ba_nr       = $daten->ba_nr;
			$aend->typ         = RISAenderung::$TYP_BA_TERMIN;
			$aend->datum       = new CDbExpression("NOW()");
			$aend->aenderungen = $aenderungen;
			$aend->save();

		}

		foreach ($dokumente as $dok) {
			$aenderungen .= AntragDokument::create_if_necessary(AntragDokument::$TYP_BA_TERMIN, $daten, $dok);
		}

	}

	public function parseSeite($seite, $alle = false)
	{
		if (RATSINFORMANT_CALL_MODE != "cron") echo "BA-Termin Seite $seite\n";
		$add  = ($alle ? "" : "&txtVon=" . date("d.m.Y", time() - 24 * 3600 * 180) . "&txtBis=" . date("d.m.Y", time() + 24 * 3600 * 356 * 2));
		$text = RISTools::load_file("http://www.ris-muenchen.de/RII2/BA-RII/ba_sitzungen.jsp?Start=$seite" . $add);
		$txt  = explode("<table class=\"ergebnistab\" ", $text);
		$txt  = explode("<!-- tabellenfuss", $txt[1]);

		preg_match_all("/ba_sitzungen_details\.jsp\?Id=([0-9]+)[\"'& ]/siU", $txt[0], $matches);
		for ($i = count($matches[1]) - 1; $i >= 0; $i--) {
			$this->parse($matches[1][$i]);
		}

		sleep(5); // Scheint ziemlich aufwändig auf der RIS-Seite zu sein, mal lieber nicht überlasten :)
	}

	public function parseAlle()
	{
		$anz = 4750;
		for ($i = $anz; $i >= 0; $i -= 10) {
			if (RATSINFORMANT_CALL_MODE != "cron") echo ($anz - $i) . " / $anz\n";
			$this->parseSeite($i);
		}
	}


	public function parseUpdate()
	{
		echo "Updates: BA-Termine\n";
		for ($i = 300; $i >= 0; $i -= 10) {
			$this->parseSeite($i);
		}
	}

}