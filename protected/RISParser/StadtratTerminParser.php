<?php

class StadtratTerminParser extends RISParser
{
	private static $MAX_OFFSET = 4900;

	public function parse($termin_id)
	{
		$termin_id = IntVal($termin_id);
		if (RATSINFORMANT_CALL_MODE != "cron") echo "- Termin $termin_id\n";

		$html_details   = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_sitzung_detail.jsp?risid=$termin_id");
		$html_dokumente = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_sitzung_dokumente.jsp?risid=$termin_id");
		$html_to        = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_sitzung_to.jsp?risid=$termin_id");
		$html_to_geheim = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_sitzung_nto.jsp?risid=$termin_id");

		$daten                         = new Termin();
		$daten->id                     = $termin_id;
		$daten->datum_letzte_aenderung = new CDbExpression('NOW()');
		$daten->gremium_id             = NULL;
		$daten->ba_nr                  = NULL;

		if (preg_match("/ris_gremien_detail\.jsp\?risid=([0-9]+)[\"'& ]/siU", $html_details, $matches)) $daten->gremium_id = IntVal($matches[1]);
		if ($daten->gremium_id) {
			$gr = Gremium::model()->findByPk($daten->gremium_id);
			if (!$gr) {
				echo "Lege Gremium an: " . $daten->gremium_id . "\n";
				Gremium::parse_stadtrat_gremien($daten->gremium_id);
			}
		}

		$geloescht            = false;
		$sitzungsort_gefunden = false;

		if (preg_match("/Sitzungsort:.*detail_div\">([^<]*)[<]/siU", $html_details, $matches)) {
			$sitzungsort_gefunden = true;
			$daten->sitzungsort   = trim(str_replace("&nbsp;", "", $matches[1]));
		}
		if (preg_match("/chste Sitzung:.*ris_sitzung_detail\.jsp\?risid=([0-9]+)[\"'& ]/siU", $html_details, $matches)) $daten->termin_next_id = trim(str_replace("&nbsp;", "", $matches[1]));
		if (preg_match("/Letzte Sitzung:.*ris_sitzung_detail\.jsp\?risid=([0-9]+)[\"'& ]/siU", $html_details, $matches)) $daten->termin_prev_id = trim(str_replace("&nbsp;", "", $matches[1]));
		if (preg_match("/Wahlperiode:.*detail_div_left_long\">([^>]*)<\//siU", $html_details, $matches)) $daten->wahlperiode = trim(str_replace("&nbsp;", "", $matches[1]));
		if (preg_match("/Status:.*detail_div_left_long\">([^>]*)<\//siU", $html_details, $matches)) $daten->status = trim(str_replace("&nbsp;", "", $matches[1]));
		if (preg_match("/diges Referat:.*detail_div_left_long\">(<a[^>]+>)?([^>]*)<\//siU", $html_details, $matches)) $daten->referat = trim(str_replace("&nbsp;", "", $matches[2]));
		if (preg_match("/Referent\/in:.*detail_div_left_long\">([^>]*)<\//siU", $html_details, $matches)) $daten->referent = trim(str_replace("&nbsp;", "", $matches[1]));
		if (preg_match("/Vorsitz:.*detail_div_left_long\">([^>]*)<\//siU", $html_details, $matches)) $daten->vorsitz = trim(str_replace("&nbsp;", "", $matches[1]));

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
			if (isset($x[1])) {
				$tag = IntVal($x[1]);
				if ($tag < 10) $tag = "0" . IntVal($tag);
				$jahr  = IntVal($x[2]);
				$y     = explode(".", $x[1]);
				$monat = $MONATE[mb_strtolower($y[1])];
				if ($monat < 10) $monat = "0" . IntVal($monat);
				$zeit          = $x[3];
				$daten->termin = "${jahr}-${monat}-${tag} ${zeit}:00";
			} else {
				if ($sitzungsort_gefunden && $daten->gremium === null && $daten->sitzungsort == "" && $daten->status == "") $geloescht = true;
				else {
					RISTools::send_email(Yii::app()->params['adminEmail'], "Stadtratstermin: Unbekanntes Datum", "ID: $termin_id\n" . print_r($matches, true));
					die();
				}
			}
		}

		$dokumente = array();

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
			if ($geloescht) {
				$aenderungen = "gelöscht";
				$changed     = true;
			} else {
				if ($alter_eintrag->termin != $daten->termin) $aenderungen .= "Termin: " . $alter_eintrag->termin . " => " . $daten->termin . "\n";
				if ($alter_eintrag->gremium_id != $daten->gremium_id) $aenderungen .= "Gremium-ID: " . $alter_eintrag->gremium_id . " => " . $daten->gremium_id . "\n";
				if ($alter_eintrag->sitzungsort != $daten->sitzungsort) $aenderungen .= "Sitzungsort: " . $alter_eintrag->sitzungsort . " => " . $daten->sitzungsort . "\n";
				if ($alter_eintrag->termin_next_id != $daten->termin_next_id) $aenderungen .= "Nächster Termin: " . $alter_eintrag->termin_next_id . " => " . $daten->termin_next_id . "\n";
				if ($alter_eintrag->termin_prev_id != $daten->termin_prev_id) $aenderungen .= "Voriger Termin: " . $alter_eintrag->termin_prev_id . " => " . $daten->termin_prev_id . "\n";
				if ($alter_eintrag->wahlperiode != $daten->wahlperiode) $aenderungen .= "Wahlperiode: " . $alter_eintrag->wahlperiode . " => " . $daten->wahlperiode . "\n";
				if ($alter_eintrag->referat != $daten->referat) $aenderungen .= "Referat: " . $alter_eintrag->referat . " => " . $daten->referat . "\n";
				if ($alter_eintrag->referent != $daten->referent) $aenderungen .= "Referent: " . $alter_eintrag->referent . " => " . $daten->referent . "\n";
				if ($alter_eintrag->vorsitz != $daten->vorsitz) $aenderungen .= "Vorsitz: " . $alter_eintrag->vorsitz . " => " . $daten->vorsitz . "\n";
				if ($aenderungen != "") $changed = true;
			}
		}


		$match_top          = "tdborder\">(?<top>.*)<\/t[hd]>";
		$match_betreff      = "tdborder\">(?<betreff>.*)<\/t[hd]>";
		$match_vorlage      = "<t(?<ueberschrift>[hd])[^>]*>(?<vorlage_holder>.*)<\/t[hd]>";
		$match_referentIn   = "<t[hd][^>]*>(?<referentIn>.*)<\/t[hd]>";
		$match_entscheidung = "<t[hd][^>]*>(?<entscheidung>.*)<\/t[hd]>";
		preg_match_all("/<tr class=\"ergebnistab_tr\">.*${match_top}.*${match_betreff}.*${match_vorlage}.*${match_referentIn}.*${match_entscheidung}.*<\/tr>/siU", $html_to, $matches);

		$bisherige_tops          = ($alter_eintrag ? $alter_eintrag->antraegeErgebnisse : array());
		$aenderungen_tops        = "";
		$verwendete_top_betreffs = array();
		$verwendete_ergebnis_ids = array();
		$abschnitt_nr            = 0;

		for ($i = 0; $i < count($matches[0]); $i++) {
			$top     = trim(str_replace(array("&nbsp;", "<strong>", "</strong>"), array(" ", "", ""), $matches["top"][$i]));
			$betreff = static::text_clean_spaces($matches["betreff"][$i]);
			if ($matches["ueberschrift"][$i] == "h") {
				$abschnitt_nr     = $abschnitt_nr + 1;
				$top_ueberschrift = true;
				$top_nr           = $abschnitt_nr;
				$betreff          = str_replace(array("<strong>", "</strong>"), array("", ""), $betreff);
			} else {
				if ($abschnitt_nr == 0) $abschnitt_nr = 1;
				$top_ueberschrift = false;
				$top_nr           = $abschnitt_nr . "." . $top;
			}

			$vorlage_holder = trim(str_replace("&nbsp;", " ", $matches["vorlage_holder"][$i]));
			preg_match_all("/risid=(?<risid>[0-9]+)>/siU", $vorlage_holder, $matches2);
			$vorlage_id = (isset($matches2["risid"][0]) ? $matches2["risid"][0] : null);

			if ($vorlage_id) {
				$vorlage = Antrag::model()->findByPk($vorlage_id);
				if (!$vorlage) {
					echo "Creating: $vorlage_id\n";
					$p = new StadtratsvorlageParser();
					$p->parse($vorlage_id);
				}
			}

			$entscheidung_original = trim(str_replace("&nbsp;", " ", $matches["entscheidung"][$i]));
			$entscheidung          = trim(preg_replace("/<a[^>]*>[^<]*<\/a>/siU", "", $entscheidung_original));

			/** @var AntragErgebnis $ergebnis */
			if (is_null($vorlage_id)) {
				$ergebnis = AntragErgebnis::model()->findByAttributes(array("sitzungstermin_id" => $termin_id, "top_betreff" => $betreff));
			} else {
				$ergebnis = AntragErgebnis::model()->findByAttributes(array("sitzungstermin_id" => $termin_id, "antrag_id" => $vorlage_id));
			}
			if (is_null($ergebnis)) $ergebnis = new AntragErgebnis();

			$ergebnis->datum_letzte_aenderung = new CDbExpression("NOW()");
			$ergebnis->sitzungstermin_id      = $termin_id;
			$ergebnis->sitzungstermin_datum   = $daten->termin;
			$ergebnis->top_nr                 = $top_nr;
			$ergebnis->antrag_id              = $vorlage_id;
			$ergebnis->top_ueberschrift       = ($top_ueberschrift ? 1 : 0);
			if ($ergebnis->entscheidung != $entscheidung) {
				$aenderungen .= "Entscheidung: " . $ergebnis->entscheidung . " => " . $entscheidung . "\n";
				$ergebnis->entscheidung = $entscheidung;
			}
			$ergebnis->top_betreff     = $betreff;
			$ergebnis->gremium_id      = $daten->gremium_id;
			$ergebnis->gremium_name    = $daten->gremium->name;

			if (!is_null($vorlage_id)) {
				$html_vorlage_ergebnis = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_vorlagen_ergebnisse.jsp?risid=$vorlage_id");
				preg_match_all("/ris_sitzung_to.jsp\?risid=" . $termin_id . ".*<\/td>.*<\/td>.*tdborder\">(?<beschluss>.*)<\/td>/siU", $html_vorlage_ergebnis, $matches3);
				$beschluss = static::text_clean_spaces($matches3["beschluss"][0]);
				if ($ergebnis->beschluss_text != $beschluss) {
					$aenderungen .= "Beschluss: " . $ergebnis->beschluss_text . " => " . $beschluss . "\n";
					$ergebnis->beschluss_text = $beschluss;
				}
			}

			$ergebnis->save();
			$verwendete_top_betreffs[] = $ergebnis->top_nr . "-" . $ergebnis->top_betreff;
			$verwendete_ergebnis_ids[] = $ergebnis->id;

			preg_match_all("/<a href=(?<url>[^ ]+) title=\"(?<title>[^\"]*)\"/siU", $entscheidung_original, $matches2);
			if (isset($matches2["url"]) && count($matches2["url"]) > 0) {
				$aenderungen .= AntragDokument::create_if_necessary(AntragDokument::$TYP_STADTRAT_BESCHLUSS, $ergebnis, array("url" => $matches2["url"][0], "name" => $matches2["title"][0]));
				/** @var AntragDokument $dok */
				$dok = AntragDokument::model()->findByAttributes(array("ergebnis_id" => $ergebnis->id, "url" => $matches2["url"][0], "name" => $matches2["title"][0]));
				if ($dok && $dok->ergebnis_id != $ergebnis->id) {
					echo "Korrgiere ID\n";
					$dok->ergebnis_id = $ergebnis->id;
					$dok->save(false);
				}
			}
		}


		preg_match_all("/<tr class=\"ergebnistab_tr\">.*<strong>(?<top>[0-9]+)\..*tdborder\">(?<betreff>.*)<\/td>.*<span[^>]+>(?<vorlage_id>.*)<\/span>.*valign=\"top\">(?<referent>.*)<\/td>/siU", $html_to_geheim, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			$betreff  = strip_tags(static::text_clean_spaces($matches["betreff"][$i]));
			$referent = static::text_clean_spaces($matches["referent"][$i]);

			/** @var AntragErgebnis $ergebnis */
			$krits    = array("sitzungstermin_id" => $termin_id, "status" => "geheim", "top_betreff" => $betreff);
			$ergebnis = AntragErgebnis::model()->findByAttributes($krits);
			if (is_null($ergebnis)) {
				$ergebnis = new AntragErgebnis();
				$aenderungen .= "Neuer geheimer Tagesordnungspunkt: " . $betreff . "\n";
			}
			$ergebnis->sitzungstermin_id      = $termin_id;
			$ergebnis->sitzungstermin_datum   = $daten->termin;
			$ergebnis->datum_letzte_aenderung = new CDbExpression("NOW()");
			$ergebnis->antrag_id              = null;
			$ergebnis->status                 = "geheim";
			$ergebnis->beschluss_text         = $matches["vorlage_id"][$i];
			$ergebnis->top_nr                 = $matches["top"][$i];
			$ergebnis->top_betreff            = $betreff;
			$ergebnis->entscheidung           = $referent;
			$ergebnis->gremium_id             = $daten->gremium_id;
			$ergebnis->gremium_name           = $daten->gremium->name;
			$ergebnis->save();

			$verwendete_top_betreffs[] = "geheim-" . $ergebnis->top_nr . "-" . $ergebnis->top_betreff;
			$verwendete_ergebnis_ids[] = $ergebnis->id;
		}

		foreach ($bisherige_tops as $top) {
			$top_key = ($top->status == "geheim" ? "geheim-" : "") . $top->top_nr . "-" . $top->top_betreff;
			if (!in_array($top_key, $verwendete_top_betreffs) && !in_array($top->id, $verwendete_ergebnis_ids)) {
				$aenderungen_tops .= "TOP entfernt: " . $top->top_nr . ":" . $top->top_betreff . "\n";
				$top->delete();
			}
		}

		if ($aenderungen_tops != "") $changed = true;


		if ($changed) {
			if (!$alter_eintrag) $aenderungen = "Neu angelegt\n";
			$aenderungen .= $aenderungen_tops;

			echo "StR-Termin $termin_id: Verändert: " . $aenderungen . "\n";

			if ($alter_eintrag) {
				$alter_eintrag->copyToHistory();
				$alter_eintrag->setAttributes($daten->getAttributes());
				if (!$alter_eintrag->save(false)) {
					RISTools::send_email(Yii::app()->params['adminEmail'], "Stadtratstermin: Nicht gespeichert", "StadtratTerminParser 1\n" . print_r($alter_eintrag->getErrors(), true));
					die("Fehler");
				}
				$daten = $alter_eintrag;

				if ($geloescht) {
					echo "Lösche";
					if (!$daten->delete()) {
						RISTools::send_email(Yii::app()->params['adminEmail'], "Stadtratstermin: Nicht gelöscht", "StadtratTerminParser 2\n" . print_r($daten->getErrors(), true));
						die("Fehler");
					}
					$aend              = new RISAenderung();
					$aend->ris_id      = $daten->id;
					$aend->ba_nr       = NULL;
					$aend->typ         = RISAenderung::$TYP_STADTRAT_TERMIN;
					$aend->datum       = new CDbExpression("NOW()");
					$aend->aenderungen = $aenderungen;
					$aend->save();
					return;
				}

			} else {
				if (!$daten->save()) {
					RISTools::send_email(Yii::app()->params['adminEmail'], "Stadtratstermin: Nicht gespeichert", "StadtratTerminParser 3\n" . print_r($daten->getErrors(), true));
					die("Fehler");
				}
			}
		}


		foreach ($dokumente as $dok) {
			$aenderungen .= AntragDokument::create_if_necessary(AntragDokument::$TYP_STADTRAT_TERMIN, $daten, $dok);
		}


		if ($aenderungen != "") {
			$aend              = new RISAenderung();
			$aend->ris_id      = $daten->id;
			$aend->ba_nr       = NULL;
			$aend->typ         = RISAenderung::$TYP_STADTRAT_TERMIN;
			$aend->datum       = new CDbExpression("NOW()");
			$aend->aenderungen = $aenderungen;
			$aend->save();

			/** @var Termin $termin */
			$termin                         = Termin::model()->findByPk($termin_id);
			$termin->datum_letzte_aenderung = new CDbExpression('NOW()'); // Auch bei neuen Dokumenten
			$termin->save();
		}

	}

	public function parseSeite($seite, $first, $alle = false)
	{
		$add  = ($alle ? "" : "&txtVon=" . date("d.m.Y", time() - 24 * 3600 * 180) . "&txtBis=" . date("d.m.Y", time() + 24 * 3600 * 356 * 2));
		$text = RISTools::load_file("http://www.ris-muenchen.de/RII2/RII/ris_sitzung_trefferliste.jsp?txtPosition=$seite" . $add);

		$txt = explode("<table class=\"ergebnistab\" ", $text);
		if ($seite > 4790 && count($txt) == 1) return;

		$txt = explode("<!-- tabellenfuss", $txt[1]);

		preg_match_all("/ris_sitzung_detail\.jsp\?risid=([0-9]+)[\"'& ]/siU", $txt[0], $matches);

		if ($first && count($matches[1]) > 0) RISTools::send_email(Yii::app()->params['adminEmail'], "Stadtratstermin VOLL", "Erste Seite voll: $seite");

		for ($i = count($matches[1]) - 1; $i >= 0; $i--) {
			$this->parse($matches[1][$i]);
		}

		sleep(5); // Scheint ziemlich aufwändig auf der RIS-Seite zu sein, mal lieber nicht überlasten :)
	}

	public function parseAlle()
	{
		$anz   = StadtratTerminParser::$MAX_OFFSET;
		$first = true;
		for ($i = $anz; $i >= 0; $i -= 10) {
			if (RATSINFORMANT_CALL_MODE != "cron") echo ($anz - $i) . " / $anz\n";
			$this->parseSeite($i, $first, true);
			$first = false;
		}
	}

	public function parseUpdate()
	{
		echo "Updates: Stadtratstermin\n";
		for ($i = 0; $i < 300; $i += 10) {
			$this->parseSeite(StadtratTerminParser::$MAX_OFFSET - $i, false, false);
		}
	}
}