<?php

class IndexController extends RISBaseController
{

	public static $BA_DOKUMENTE_TAGE_PRO_SEITE = 14;

	/**
	 * @param int $width
	 * @param int $zoom
	 * @param int $x
	 * @param int $y
	 */
	public function actionTileCache($width, $zoom, $x, $y)
	{

		if ($width == 256) {
			$boundaries = array(
				3  => array(2, 2, 6, 3),
				4  => array(6, 4, 11, 6),
				5  => array(14, 10, 19, 11),
				6  => array(31, 21, 36, 22),
				7  => array(66, 43, 70, 45),
				8  => array(134, 88, 138, 89),
				9  => array(270, 176, 274, 178),
				10 => array(542, 354, 547, 356),
				11 => array(1086, 708, 1091, 712),
			);
			if (isset($boundaries[$zoom])) {
				$bound       = $boundaries[$zoom];
				$outofbounds = false;
				if ($x < $bound[0] || $y < $bound[1] || $x > $bound[2] || $y > $bound[3]) $outofbounds = true;

				if ($outofbounds) {
					Header("Location: /images/HereBeDragons256.png");
					Yii::app()->end();
				}
			}
		}

		if ($width == 256) {
			$array = array("1", "2", "3");
			$key   = $array[array_rand($array)] . "-" . Yii::app()->params['skobblerKey'];
			$url   = "http://tiles" . $key . ".skobblermaps.com/TileService/tiles/2.0/00022210100/0/${zoom}/${x}/${y}.png";
		} else {
			$array = array("1", "2", "3");
			$key   = $array[array_rand($array)] . "-" . Yii::app()->params['skobblerKey'];
			$url   = "http://tiles" . $key . ".skobblermaps.com/TileService/tiles/2.0/00022210100/0/${zoom}/${x}/${y}.png@2x";
		}

		$fp = fopen("/tmp/tiles.log", "a");
		fwrite($fp, $url . "\n");
		fclose($fp);


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$string = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($status == 200 && $string != "") {
			if (!file_exists(TILE_CACHE_DIR . "$width")) mkdir(TILE_CACHE_DIR . "$width", 0775);
			if (!file_exists(TILE_CACHE_DIR . "$width/$zoom")) mkdir(TILE_CACHE_DIR . "$width/$zoom", 0775);
			if (!file_exists(TILE_CACHE_DIR . "$width/$zoom/$x")) mkdir(TILE_CACHE_DIR . "$width/$zoom/$x", 0775);
			file_put_contents(TILE_CACHE_DIR . "$width/$zoom/$x/$y.png", $string);
			Header("Content-Type: image/png");
			echo $string;
		} else {
			Header("Content-Type: text/plain");
			echo $status;
			var_dump($ch);
		}
		Yii::app()->end();
	}

	public function actionFeed()
	{
		if (isset($_REQUEST["krit_typ"])) {
			$krits = RISSucheKrits::createFromUrl($_REQUEST);
			$titel = "Ratsinformant: " . $krits->getTitle();

			$solr   = RISSolrHelper::getSolrClient("ris");
			$select = $solr->createSelect();

			$krits->addKritsToSolr($select);

			$select->setRows(100);
			$select->addSort('sort_datum', $select::SORT_DESC);

			/** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
			$hl = $select->getHighlighting();
			$hl->setFields('text, text_ocr, antrag_betreff');
			$hl->setSimplePrefix('<b>');
			$hl->setSimplePostfix('</b>');

			$ergebnisse = $solr->select($select);

			$data = RISSolrHelper::ergebnisse2FeedData($ergebnisse);
		} else {
			$data = array();
			/** @var array|RISAenderung[] $aenderungen */
			$aenderungen = RISAenderung::model()->findAll(array("order" => "id DESC", "limit" => 100));
			foreach ($aenderungen as $aenderung) $data[] = $aenderung->toFeedData();
			$titel = "Ratsinformant Änderungen";
		}

		$this->render("feed", array(
			"feed_title"       => $titel,
			"feed_description" => $titel,
			"data"             => $data,
		));
	}

	public function actionAjaxEmailIstRegistriert($email)
	{
		$person = BenutzerIn::model()->findAll(array(
			"condition" => "email='" . addslashes($email) . "' AND pwd_enc != ''"
		));
		if (count($person) > 0) {
			/** @var BenutzerIn $p */
			$p = $person[0];
			if ($p->email_bestaetigt) echo "1";
			else echo "0";
		} else {
			echo "-1";
		}
	}

	/**
	 * @param RISSucheKrits $curr_krits
	 * @param string $code
	 * @return array
	 */
	protected function sucheBenachrichtigungenAnmelden($curr_krits, $code)
	{
		$user = Yii::app()->getUser();

		$correct_person      = null;
		$wird_benachrichtigt = false;

		list($msg_ok, $msg_err) = $this->performLoginActions();

		if (!$user->isGuest) {
			/** @var BenutzerIn $ich */
			$ich = BenutzerIn::model()->findByAttributes(array("email" => Yii::app()->user->id));

			if (AntiXSS::isTokenSet("benachrichtigung_add")) {
				$ich->addBenachrichtigung($curr_krits);
			}
			if (AntiXSS::isTokenSet("benachrichtigung_del")) {
				$ich->delBenachrichtigung($curr_krits);
			}

			$wird_benachrichtigt = $ich->wirdBenachrichtigt($curr_krits);
		}


		if ($user->isGuest) {
			$ich              = null;
			$eingeloggt       = false;
			$email_angegeben  = false;
			$email_bestaetigt = false;
		} else {
			$eingeloggt = true;
			/** @var BenutzerIn $ich */
			if (!$ich) $ich = BenutzerIn::model()->findByAttributes(array("email" => Yii::app()->user->id));
			if ($ich->email == "") {
				$email_angegeben  = false;
				$email_bestaetigt = false;
			} elseif ($ich->email_bestaetigt) {
				$email_angegeben  = true;
				$email_bestaetigt = true;
			} else {
				$email_angegeben  = true;
				$email_bestaetigt = false;
			}
		}

		return array(
			"eingeloggt"          => $eingeloggt,
			"email_angegeben"     => $email_angegeben,
			"email_bestaetigt"    => $email_bestaetigt,
			"wird_benachrichtigt" => $wird_benachrichtigt,
			"ich"                 => $ich,
			"msg_err"             => $msg_err,
			"msg_ok"              => $msg_ok,
		);
	}


	/**
	 * @param AntragDokument[] $dokumente
	 * @param null|RISSucheKrits $filter_krits
	 * @return array
	 */
	protected function dokumente2geodata(&$dokumente, $filter_krits = null)
	{
		$geodata = array();
		foreach ($dokumente as $dokument) {
			if ($dokument->antrag) {
				$link = $dokument->antrag->getLink();
				$name = $dokument->antrag->getName();
			} elseif ($dokument->termin) {
				$link = $dokument->termin->getLink();
				$name = $dokument->termin->getName();
			} else {
				$link = $name = "";
			}
			if (strlen($name) > 150) $name = substr($name, 0, 148) . "...";
			if ($link != "") $link = "<div class='antraglink'>" . CHtml::link($name, $link) . "</div>";
			foreach ($dokument->orte as $ort) if ($ort->ort->to_hide == 0 && ($filter_krits === null || $filter_krits->filterGeo($ort->ort))) {
				$str = $link;
				$str .= "<div class='ort_dokument'>";
				$str .= "<div class='ort'>" . CHtml::encode($ort->ort->ort) . "</div>";
				$str .= "<div class='dokument'>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</div>";
				$str .= "</div>";
				$geodata[] = array(
					FloatVal($ort->ort->lat),
					FloatVal($ort->ort->lon),
					$str
				);
			}
		}
		return $geodata;
	}

	/**
	 * @param RISSucheKrits $krits
	 * @param \Solarium\QueryType\Select\Result\Result $ergebnisse
	 * @return array
	 */
	protected function getJSGeodata($krits, $ergebnisse)
	{
		$geo = $krits->getGeoKrit();
		/** @var RISSolrDocument[] $solr_dokumente */
		$solr_dokumente = $ergebnisse->getDocuments();
		$dokument_ids   = array();
		foreach ($solr_dokumente as $dokument) {
			$x              = explode(":", $dokument->id);
			$dokument_ids[] = IntVal($x[1]);
		}
		$geodata = array();
		if (count($dokument_ids) > 0) {
			$lat        = FloatVal($geo["lat"]);
			$lng        = FloatVal($geo["lng"]);
			$dist_field = "(((acos(sin(($lat*pi()/180)) * sin((lat*pi()/180))+cos(($lat*pi()/180)) * cos((lat*pi()/180)) * cos((($lng- lon)*pi()/180))))*180/pi())*60*1.1515*1.609344) <= " . FloatVal($geo["radius"] / 1000);
			$SQL        = "select a.dokument_id, b.* FROM antraege_orte a JOIN orte_geo b ON a.ort_id = b.id WHERE a.dokument_id IN (" . implode(", ", $dokument_ids) . ") AND b.to_hide = 0 AND $dist_field";
			$result     = Yii::app()->db->createCommand($SQL)->queryAll();
			foreach ($result as $geo) {
				/** @var AntragDokument $dokument */
				$dokument = AntragDokument::model()->findByPk($geo["dokument_id"]);

				if ($dokument->antrag) {
					$link = $dokument->antrag->getLink();
					$name = $dokument->antrag->getName();
				} elseif ($dokument->termin) {
					$link = $dokument->termin->getLink();
					$name = $dokument->termin->getName();
				} else {
					$link = $name = "";
				}
				if (strlen($name) > 150) $name = substr($name, 0, 148) . "...";
				if ($link != "") $link = "<div class='antraglink'>" . CHtml::link($name, $link) . "</div>";
				$str = $link;
				$str .= "<div class='ort_dokument'>";
				$str .= "<div class='ort'>" . CHtml::encode($geo["ort"]) . "</div>";
				$str .= "<div class='dokument'>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</div>";
				$str .= "</div>";
				$geodata[] = array(
					FloatVal($geo["lat"]),
					FloatVal($geo["lon"]),
					$str
				);
			}

		}
		return $geodata;
	}


	/**
	 * @param Antrag[] $antraege
	 * @param int $typ
	 * @return array
	 */
	protected function antraege2geodata(&$antraege, $typ = 0)
	{
		$geodata          = $geodata_overflow = array();
		$geodata_nach_dok = array();
		foreach ($antraege as $ant) {
			foreach ($ant->dokumente as $dokument) {
				foreach ($dokument->orte as $ort) if ($ort->ort->to_hide == 0) {
					$name = $ant->getName();
					if (strlen($name) > 150) $name = substr($name, 0, 148) . "...";
					$str = "<div class='antraglink'>" . CHtml::link($name, $ant->getLink()) . "</div>";
					$str .= "<div class='ort_dokument'>";
					$str .= "<div class='ort'>" . CHtml::encode($ort->ort->ort) . "</div>";
					$str .= "<div class='dokument'>" . CHtml::link($dokument->name, $dokument->getOriginalLink()) . "</div>";
					$str .= "</div>";
					$str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');

					if (!isset($geodata_nach_dok[$dokument->id])) $geodata_nach_dok[$dokument->id] = array();
					$geodata_nach_dok[$dokument->id][] = array(
						FloatVal($ort->ort->lat),
						FloatVal($ort->ort->lon),
						$str,
						$typ
					);
				}
			}
		}
		foreach ($geodata_nach_dok as $dok_geo) if (count($dok_geo) >= 20) {
			$geodata_overflow[] = $dok_geo;
		} else {
			foreach ($dok_geo as $d) $geodata[] = $d;
		}

		return array($geodata, $geodata_overflow);
	}

	/**
	 * @param float $lat
	 * @param float $lng
	 */
	public function actionGeo2Address($lat, $lng)
	{
		Header("Content-Type: application/json; charset=UTF-8");
		$naechster_ort = OrtGeo::findClosest($lng, $lat);
		echo json_encode(array(
			"ort_name" => $naechster_ort->ort,
		));
		Yii::app()->end();
	}


	/**
	 * @param float $lat
	 * @param float $lng
	 * @param float $radius
	 * @param int $seite
	 */
	public function actionAntraegeAjaxGeo($lat, $lng, $radius, $seite = 0)
	{
		$krits = new RISSucheKrits();
		$krits->addGeoKrit($lng, $lat, $radius);

		$solr   = RISSolrHelper::getSolrClient("ris");
		$select = $solr->createSelect();

		$krits->addKritsToSolr($select);

		$select->setStart(30 * $seite);
		$select->setRows(30);
		$select->addSort('sort_datum', $select::SORT_DESC);

		$ergebnisse = $solr->select($select);

		/** @var Antrag[] $antraege */
		$antraege = array();
		/** @var RISSolrDocument[] $solr_dokumente */
		$solr_dokumente = $ergebnisse->getDocuments();
		$dokument_ids   = array();
		foreach ($solr_dokumente as $dokument) {
			$x              = explode(":", $dokument->id);
			$dokument_ids[] = IntVal($x[1]);
		}
		foreach ($dokument_ids as $dok_id) {
			/** @var AntragDokument $ant */
			$ant = AntragDokument::model()->with(array(
				"antrag"           => array(),
				"antrag.dokumente" => array(
					"alias"     => "dokumente_2",
					"condition" => "dokumente_2.id IN (" . implode(", ", $dokument_ids) . ")"
				)
			))->findByPk($dok_id);
			if ($ant->antrag) {
				$antraege[$ant->antrag_id] = $ant->antrag;
			}
		}

		$geodata       = $this->getJSGeodata($krits, $ergebnisse);
		$naechster_ort = OrtGeo::findClosest($lng, $lat);
		ob_start();

		$this->renderPartial('index_antraege_liste', array(
			"aeltere_url_ajax"  => $this->createUrl("index/antraegeAjaxGeo", array("lat" => $lat, "lng" => $lng, "radius" => $radius, "seite" => ($seite + 1))),
			"aeltere_url_std"   => $this->createUrl("index/antraegeStdGeo", array("lat" => $lat, "lng" => $lng, "radius" => $radius, "seite" => ($seite + 1))),
			"neuere_url_ajax"   => null,
			"neuere_url_std"    => null,
			"antraege"          => $antraege,
			"geo_lng"           => $lng,
			"geo_lat"           => $lat,
			"radius"            => $radius,
			"naechster_ort"     => $naechster_ort,
			"weiter_links_oben" => true,
		));

		Header("Content-Type: application/json; charset=UTF-8");
		echo json_encode(array(
			"datum"         => date("Y-m-d"),
			"html"          => ob_get_clean(),
			"geodata"       => $geodata,
			"krit_str"      => $krits->getJson(),
			"naechster_ort" => $naechster_ort->ort
		));
		Yii::app()->end();
	}


	public function actionSuche($code = "")
	{
		if (AntiXSS::isTokenSet("search_form")) {
			$krits           = new RISSucheKrits();
			if (trim($_REQUEST["volltext"]) != "") $krits->addVolltextsucheKrit($_REQUEST["volltext"]);
			if (trim($_REQUEST["antrag_nr"]) != "") $krits->addAntragNrKrit($_REQUEST["antrag_nr"]);
			if ($_REQUEST["typ"] != "") $krits->addAntragTypKrit($_REQUEST["typ"]);
		} elseif (isset($_REQUEST["suchbegriff"])) {
			$suchbegriff     = $_REQUEST["suchbegriff"];
			$this->suche_pre = $suchbegriff;
			$krits           = new RISSucheKrits();
			$krits->addVolltextsucheKrit($suchbegriff);
		} else {
			$krits       = RISSucheKrits::createFromUrl($_REQUEST);
			$suchbegriff = $krits->getTitle();
		}

		$this->load_leaflet_css = true;

		if ($krits->getKritsCount() > 0) {

			$benachrichtigungen_optionen = $this->sucheBenachrichtigungenAnmelden($krits, $code);


			$solr   = RISSolrHelper::getSolrClient("ris");
			$select = $solr->createSelect();

			$krits->addKritsToSolr($select);


			$select->setRows(50);
			$select->addSort('sort_datum', $select::SORT_DESC);

			/** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
			$hl = $select->getHighlighting();
			$hl->setFields('text, text_ocr, antrag_betreff');
			$hl->setSimplePrefix('<b>');
			$hl->setSimplePostfix('</b>');

			$facetSet = $select->getFacetSet();
			$facetSet->createFacetField('antrag_typ')->setField('antrag_typ');
			$facetSet->createFacetField('antrag_wahlperiode')->setField('antrag_wahlperiode');

			$ergebnisse = $solr->select($select);

			if ($krits->isGeoKrit()) $geodata = $this->getJSGeodata($krits, $ergebnisse);
			else $geodata = null;

			$this->render("suchergebnisse", array_merge(array(
				"krits"       => $krits,
				"ergebnisse"  => $ergebnisse,
				"geodata"     => $geodata,
			), $benachrichtigungen_optionen));

		} else {
			$this->render("suche");
		}
	}


	public function actionDokument($id)
	{
		/** @var AntragDokument $dokument */
		$dokument = AntragDokument::model()->findByPk($id);
		try {
			$morelikethis = $dokument->solrMoreLikeThis();
		} catch (Exception $e) {
			$morelikethis = null;
		}
		$this->render("dokument_intern", array(
			"dokument"     => $dokument,
			"morelikethis" => $morelikethis,
		));
	}

	/**
	 * @param int $ba_nr
	 * @param string $datum_max
	 * @return array
	 */
	private function ba_dokumente_nach_datum($ba_nr, $datum_max)
	{
		if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/siu", $datum_max)) {
			$datum_bis = $datum_max;
			$datum_von = date("Y-m-d", RISTools::date_iso2timestamp($datum_max) - static::$BA_DOKUMENTE_TAGE_PRO_SEITE * 24 * 3600);
		} else {
			$datum_bis = date("Y-m-d");
			$datum_von = date("Y-m-d", time() - static::$BA_DOKUMENTE_TAGE_PRO_SEITE * 24 * 3600);
		}

		/** @var array|Antrag[] $antraege1 */
		$antraege1 = Antrag::model()->neueste_stadtratsantragsdokumente($ba_nr, $datum_von . " 00:00:00", $datum_bis . " 23:59:59")->findAll();
		/** @var array|Antrag[] $antraege2 */
		$antraege2 = Antrag::model()->neueste_stadtratsantragsdokumente_geo($ba_nr, $datum_von . " 00:00:00", $datum_bis . " 23:59:59")->findAll();

		$antraege = $antraege1;
		$a_ids    = array();
		foreach ($antraege1 as $a) $a_ids[] = $a->id;
		foreach ($antraege2 as $a) if (!in_array($a->id, $a_ids)) $antraege[] = $a;
		usort($antraege, function ($a1, $a2) {
			/** @var Antrag $a1 */
			/** @var Antrag $a2 */
			$ts1 = $a1->neuestes_dokument_ts();
			$ts2 = $a2->neuestes_dokument_ts();
			if ($ts1 > $ts2) return -1;
			if ($ts1 < $ts2) return 1;
			return 0;
		});

		list($geodata1, $geodata_overflow1) = $this->antraege2geodata($antraege1);
		list($geodata2, $geodata_overflow2) = $this->antraege2geodata($antraege2, 1);
		$geodata          = array_merge($geodata1, $geodata2);
		$geodata_overflow = array_merge($geodata_overflow1, $geodata_overflow2);

		$aeltere_url_ajax = $this->createUrl("index/baAntraegeAjaxDatum", array("ba_nr" => $ba_nr, "datum_max" => date("Y-m-d", RISTools::date_iso2timestamp($datum_bis) - 14 * 24 * 3600)));
		$aeltere_url_std  = $this->createUrl("index/ba", array("ba_nr" => $ba_nr, "datum_max" => date("Y-m-d", RISTools::date_iso2timestamp($datum_bis) - 14 * 24 * 3600)));
		$neuere_url_ajax  = ($datum_bis != date("Y-m-d") ? $this->createUrl("index/baAntraegeAjaxDatum", array("ba_nr" => $ba_nr, "datum_max" => date("Y-m-d", RISTools::date_iso2timestamp($datum_bis) + 14 * 24 * 3600))) : null);
		$neuere_url_std   = ($datum_bis != date("Y-m-d") ? $this->createUrl("index/ba", array("ba_nr" => $ba_nr, "datum_max" => date("Y-m-d", RISTools::date_iso2timestamp($datum_bis) + 14 * 24 * 3600))) : null);
		return array(
			"datum_von"        => $datum_von,
			"datum_bis"        => $datum_bis,
			"aeltere_url_ajax" => $aeltere_url_ajax,
			"aeltere_url_std"  => $aeltere_url_std,
			"neuere_url_ajax"  => $neuere_url_ajax,
			"neuere_url_std"   => $neuere_url_std,
			"antraege"         => $antraege,
			"geodata"          => $geodata,
			"geodata_overflow" => $geodata_overflow,
		);
	}

	/**
	 * @param int $ba_nr
	 * @param string $datum_max
	 */
	public function actionBaAntraegeAjaxDatum($ba_nr, $datum_max)
	{
		$data = $this->ba_dokumente_nach_datum($ba_nr, $datum_max);

		ob_start();
		$this->renderPartial('index_antraege_liste', array_merge(array(
			"weiter_links_oben" => true,
		), $data));

		Header("Content-Type: application/json; charset=UTF-8");
		echo json_encode(array(
			"datum_von"        => $data["datum_von"],
			"datum_bis"        => $data["datum_bis"],
			"html"             => ob_get_clean(),
			"geodata"          => $data["geodata"],
			"geodata_overflow" => $data["geodata_overflow"],
		));
		Yii::app()->end();
	}


	/**
	 * @param int $ba_nr
	 * @param string $datum_max
	 */
	public function actionBa($ba_nr, $datum_max = "")
	{
		$this->top_menu = "ba";

		$this->load_leaflet_css      = true;
		$this->load_leaflet_draw_css = true;

		$tage_zukunft       = 30;
		$tage_vergangenheit = 30;

		$antraege_data = $this->ba_dokumente_nach_datum($ba_nr, $datum_max);

		$termine_zukunft       = Termin::model()->termine_stadtrat_zeitraum($ba_nr, date("Y-m-d 00:00:00", time()), date("Y-m-d 00:00:00", time() + $tage_zukunft * 24 * 3600), true)->findAll();
		$termine_vergangenheit = Termin::model()->termine_stadtrat_zeitraum($ba_nr, date("Y-m-d 00:00:00", time() - $tage_vergangenheit * 24 * 3600), date("Y-m-d H:i:s", time()), false)->findAll();
		$termin_dokumente      = Termin::model()->neueste_stadtratsantragsdokumente($ba_nr, date("Y-m-d 00:00:00", time() - $tage_vergangenheit * 24 * 3600), date("Y-m-d H:i:s", time()), false)->findAll();

		$ba = Bezirksausschuss::model()->findByPk($ba_nr);
		$this->render("ba_uebersicht", array_merge(array(
			"ba"                           => $ba,
			"termine_zukunft"              => $termine_zukunft,
			"termine_vergangenheit"        => $termine_vergangenheit,
			"termin_dokumente"             => $termin_dokumente,
			"tage_vergangenheit"           => $tage_vergangenheit,
			"tage_zukunft"                 => $tage_zukunft,
			"tage_vergangenheit_dokumente" => static::$BA_DOKUMENTE_TAGE_PRO_SEITE,
			"fraktionen"                   => StadtraetIn::getGroupedByFraktion(date("Y-m-d"), $ba_nr),
			"explizites_datum"             => ($datum_max != ""),
		), $antraege_data));
	}


	/**
	 * @param string $datum_max
	 */
	public function actionStadtratAntraegeAjaxDatum($datum_max)
	{
		$x    = explode("-", $datum_max);
		$time = mktime(0, 0, 0, $x[1], $x[2], $x[0]);

		$i = 0;
		do {
			$datum = date("Y-m-d", $time - 3600 * 24 * $i);
			/** @var array|Antrag[] $antraege */
			$antraege = Antrag::model()->neueste_stadtratsantragsdokumente(null, $datum . " 00:00:00", $datum . " 23:59:59")->findAll();
			$i++;
		} while (count($antraege) == 0);

		list($geodata, $geodata_overflow) = $this->antraege2geodata($antraege);

		ob_start();
		$this->renderPartial('index_antraege_liste', array(
			"aeltere_url_ajax"  => $this->createUrl("index/stadtratAntraegeAjaxDatum", array("datum_max" => date("Y-m-d", RISTools::date_iso2timestamp($datum . " 00:00:00") - 1))),
			"aeltere_url_std"   => $this->createUrl("index/stadtrat", array("datum_max" => date("Y-m-d", RISTools::date_iso2timestamp($datum . " 00:00:00") - 1))),
			"neuere_url_ajax"   => null,
			"neuere_url_std"    => null,
			"antraege"          => $antraege,
			"datum"             => $datum,
			"weiter_links_oben" => true,
		));

		Header("Content-Type: application/json; charset=UTF-8");
		echo json_encode(array(
			"datum"            => $datum,
			"html"             => ob_get_clean(),
			"geodata"          => $geodata,
			"geodata_overflow" => $geodata_overflow
		));
		Yii::app()->end();
	}

	/**
	 * @param string $datum
	 */
	public function actionStartseite($datum = "")
	{
		$this->top_menu = "stadtrat";
		$this->performLoginActions();

		$this->load_leaflet_css      = true;
		$this->load_leaflet_draw_css = true;

		if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/siu", $datum)) {
			$datum_von = $datum . " 00:00:00";
			$datum_bis = $datum . " 23:59:59";
			/** @var array|Antrag[] $antraege */
			$antraege          = Antrag::model()->neueste_stadtratsantragsdokumente(null, $datum_von, $datum_bis)->findAll();
			$antraege_stadtrat = $antraege_sonstige = array();
			foreach ($antraege as $ant) {
				if ($ant->ba_nr === null) $antraege_stadtrat[] = $ant;
				else $antraege_sonstige[] = $ant;
			}
		} else {
			$i = 1;
			do {
				if ($i == 1) {
					$datum_von = date("Y-m-d", time() - 3600 * 24 * $i) . " 00:00:00";
					$datum_bis = date("Y-m-d H:i:s");
				} else {
					$datum_von = date("Y-m-d", time() - 3600 * 24 * $i) . " 00:00:00";
					$datum_bis = date("Y-m-d", time() - 3600 * 24 * $i) . " 23:59:59";
				}
				/** @var array|Antrag[] $antraege */
				$antraege          = Antrag::model()->neueste_stadtratsantragsdokumente(null, $datum_von, $datum_bis)->findAll();
				$antraege_stadtrat = $antraege_sonstige = array();
				foreach ($antraege as $ant) {
					if ($ant->ba_nr === null) $antraege_stadtrat[] = $ant;
					else $antraege_sonstige[] = $ant;
				}
				$i++;
			} while (count($antraege) == 0);
		}

		list($geodata, $geodata_overflow) = $this->antraege2geodata($antraege);

		$this->render('startseite', array(
			"aeltere_url_ajax"  => $this->createUrl("index/stadtratAntraegeAjaxDatum", array("datum_max" => date("Y-m-d", RISTools::date_iso2timestamp($datum_von) - 1))),
			"aeltere_url_std"   => $this->createUrl("index/stadtrat", array("datum" => date("Y-m-d", RISTools::date_iso2timestamp($datum_von) - 1))),
			"neuere_url_ajax"   => null,
			"neuere_url_std"    => null,
			"antraege_sonstige" => $antraege_sonstige,
			"antraege_stadtrat" => $antraege_stadtrat,
			"geodata"           => $geodata,
			"geodata_overflow"  => $geodata_overflow,
			"datum"             => $datum_von,
			"explizites_datum"  => ($datum != ""),
			"statistiken"       => RISMetadaten::getStats(),
		));
	}

	/**
	 * @param int $id
	 */
	public function actionStadtraetIn($id)
	{
		/** @var StadtraetIn $stadtraetIn */
		$stadtraetIn = StadtraetIn::model()->findByPk($id);

		$this->render("stadtraetIn", array(
			"stadtraetIn" => $stadtraetIn,
		));
	}

	/**
	 * @param string $id
	 * @param string $code
	 */
	public function actionResetPassword($id = "", $code = "")
	{
		$my_url = $this->createUrl("index/resetPassword", array("id" => $id, "code" => $code));
		if (AntiXSS::isTokenSet("set")) {
			/** @var null|BenutzerIn $benutzerIn */
			$benutzerIn = BenutzerIn::model()->findByPk($id);
			if ($benutzerIn) {
				if ($_REQUEST["password"] != $_REQUEST["password2"]) {
					$this->render('reset_password_set_form', array(
						"current_url" => $my_url,
						"msg_err"     => "Die beiden Passwörter stimmen nicht überein"
					));
				} else {
					$ret = $benutzerIn->resetPasswordDo($code, $_REQUEST["password"]);
					if ($ret === true) $this->render('reset_password_done');
					else $this->render('reset_password_set_form', array(
						"current_url" => $my_url,
						"msg_err"     => $ret
					));
				}
			} else {
				$this->render('reset_password_form', array(
					"current_url" => $this->createUrl("index/resetPasswordForm"),
					"msg_err"     => "Ungültiger Aufruf (BenutzerIn nicht gefunden)"
				));
			}

		} else {
			$this->render('reset_password_set_form', array(
				"current_url" => $my_url,
				"msg_err"     => ""
			));
		}
	}

	public function actionResetPasswordForm()
	{
		if (AntiXSS::isTokenSet("pwd_reset")) {
			/** @var null|BenutzerIn $benutzerIn */
			$benutzerIn = BenutzerIn::model()->findByAttributes(array("email" => $_REQUEST["email"]));
			if ($benutzerIn) {
				$ret = $benutzerIn->resetPasswordStart();
				if ($ret === true) $this->render('reset_password_sent');
				else $this->render('reset_password_form', array(
					"current_url" => $this->createUrl("index/resetPasswordForm"),
					"msg_err"     => $ret
				));
			} else {
				$this->render('reset_password_form', array(
					"current_url" => $this->createUrl("index/resetPasswordForm"),
					"msg_err"     => "Es gibt keinen Zugang mit dieser E-Mail-Adresse"
				));
			}
		} else {
			$this->render('reset_password_form', array(
				"current_url" => $this->createUrl("index/resetPasswordForm"),
				"msg_err"     => ""
			));
		}
	}


	public function actionHighlights()
	{
		$dokumente = AntragDokument::model()->with("antrag")->findAll(array("condition" => "highlight IS NOT NULL", "order" => "highlight DESC"));
		$this->render("dokumentenliste", array("dokumente" => $dokumente));
	}


	/**
	 *
	 */
	public function actionError()
	{
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		} else {
			$this->render('error', array("code" => 400, "message" => "Ein Fehler ist aufgetreten"));
		}
	}

}
