<?php

class BenachrichtigungenController extends RISBaseController
{

	public function actionIndex($code = "")
	{
		$this->top_menu = "benachrichtigungen";

		list($msg_ok, $msg_err) = $this->requireLogin($this->createUrl("index/benachrichtigungen"), $code);


		/** @var BenutzerIn $ich */
		$ich = BenutzerIn::model()->findByAttributes(array("email" => Yii::app()->user->id));

		$this->load_leaflet_css      = true;
		$this->load_leaflet_draw_css = true;

		if (AntiXSS::isTokenSet("einstellungen_speichern")) {
			$einstellungen = $ich->getEinstellungen();
			if (isset($_REQUEST["intervall"]) && $_REQUEST["intervall"] == "tag") $einstellungen->benachrichtigungstag = null;
			if (isset($_REQUEST["intervall"]) && $_REQUEST["intervall"] == "woche")  {
				if (isset($_REQUEST["wochentag"])) $einstellungen->benachrichtigungstag = IntVal($_REQUEST["wochentag"]);
			}
			$ich->setEinstellungen($einstellungen);
			$ich->save();
			$msg_ok = "Die Einstellung wurde gespeichert.";
		}

		if (AntiXSS::isTokenSet("del_ben")) {
			foreach ($_REQUEST[AntiXSS::createToken("del_ben")] as $ben => $_val) {
				$bena = json_decode(rawurldecode($ben), true);
				$krit = new RISSucheKrits($bena);
				$ich->delBenachrichtigung($krit);
				$msg_ok = "Die Benachrichtigung wurde entfernt.";
			}
		}

		if (AntiXSS::isTokenSet("ben_add_text")) {
			$suchbegriff = trim($_REQUEST["suchbegriff"]);
			if ($suchbegriff == "") {
				$msg_err = "Bitte gib einen Suchausdruck an.";
			} else {
				$ben = new RISSucheKrits();
				$ben->addVolltextsucheKrit($suchbegriff);
				$ich->addBenachrichtigung($ben);
				$msg_ok = "Die Benachrichtigung wurde hinzugefügt.";
			}
		}

		if (AntiXSS::isTokenSet("ben_add_ba")) {
			$ben = new RISSucheKrits();
			$ben->addBAKrit($_REQUEST["ba"]);
			$ich->addBenachrichtigung($ben);
			$msg_ok = "Die Benachrichtigung wurde hinzugefügt.";
		}

		if (AntiXSS::isTokenSet("ben_add_geo")) {
			if ($_REQUEST["geo_lng"] == 0 || $_REQUEST["geo_lat"] == 0 || $_REQUEST["geo_radius"] <= 0) {
				$msg_err = "Ungültige Eingabe.";
			} else {
				$ben = new RISSucheKrits();
				$ben->addGeoKrit($_REQUEST["geo_lng"], $_REQUEST["geo_lat"], $_REQUEST["geo_radius"]);
				$ich->addBenachrichtigung($ben);
				$msg_ok = "Die Benachrichtigung wurde hinzugefügt.";
			}
		}

		if (AntiXSS::isTokenSet("del_vorgang_abo")) {
			foreach (AntiXSS::getTokenVal("del_vorgang_abo") as $vorgang_id => $_tmp) {
				/** @var Vorgang $vorgang */
				$vorgang = Vorgang::model()->findByPk($vorgang_id);
				$vorgang->deabonnieren($ich);
				$msg_ok = "Der Vorgang wurde entfernt.";
			}
		}


		$this->render("index", array(
			"ich"     => $ich,
			"msg_err" => $msg_err,
			"msg_ok"  => $msg_ok,
		));
	}

	/**
	 * @param Solarium\Client $solr
	 * @param BenutzerIn $benutzerIn
	 * @return \Solarium\QueryType\Select\Query\Query
	 */
	protected function getAlleSuchergebnisse(&$solr, $benutzerIn)
	{
		$select = $solr->createSelect();

		$select->addSort('sort_datum', $select::SORT_DESC);
		$select->setRows(100);

		/** @var Solarium\QueryType\Select\Query\Component\DisMax $dismax */
		$dismax = $select->getDisMax();
		$dismax->setQueryParser('edismax');
		$dismax->setQueryFields("text text_ocr");

		$benachrichtigungen = $benutzerIn->getBenachrichtigungen();
		$krits_solr         = array();

		foreach ($benachrichtigungen as $ben) $krits_solr[] = "(" . $ben->getSolrQueryStr($select) . ")";
		$querystr = implode(" OR ", $krits_solr);

		$select->setQuery($querystr);

		/** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
		$hl = $select->getHighlighting();
		$hl->setFields('text, text_ocr, antrag_betreff');
		$hl->setSimplePrefix('<b>');
		$hl->setSimplePostfix('</b>');

		return $select;
	}

	/**
	 * @param string $code
	 */
	public function actionAlleFeed($code)
	{
		$benutzerIn = BenutzerIn::getByFeedCode($code);
		if (!$benutzerIn) {
			$this->render('../index/error', array("code" => 400, "message" => "Das Feed konnte leider nicht gefunden werden."));
			return;
		}

		$titel       = "Suchergebnisse";
		$description = "Neue Dokumente, die einem der folgenden Kriterien entsprechen:<br>";
		$bens        = $benutzerIn->getBenachrichtigungen();
		foreach ($bens as $ben) $description .= "- " . CHtml::encode($ben->getTitle()) . "<br>";

		$solr       = RISSolrHelper::getSolrClient("ris");
		$select     = $this->getAlleSuchergebnisse($solr, $benutzerIn);
		$ergebnisse = $solr->select($select);
		$data       = RISSolrHelper::ergebnisse2FeedData($ergebnisse);

		$this->render("../index/feed", array(
			"feed_title"       => $titel,
			"feed_description" => $description,
			"data"             => $data,
		));

	}


	public function actionAlleSuchergebnisse()
	{
		$this->requireLogin($this->createUrl("index/benachrichtigungen"));

		/** @var BenutzerIn $ich */
		$ich = BenutzerIn::model()->findByAttributes(array("email" => Yii::app()->user->id));

		$solr   = RISSolrHelper::getSolrClient("ris");
		$select = $this->getAlleSuchergebnisse($solr, $ich);

		$facetSet = $select->getFacetSet();
		$facetSet->createFacetField('antrag_typ')->setField('antrag_typ');
		$facetSet->createFacetField('antrag_wahlperiode')->setField('antrag_wahlperiode');

		$ergebnisse = $solr->select($select);


		$this->render("alle_suchergebnisse", array(
			"ergebnisse" => $ergebnisse,
		));

	}


	public function actionNewsletterHTMLTest() {
		$benutzerIn = $this->aktuelleBenutzerIn();
		$data = $benutzerIn->benachrichtigungsErgebnisse(14);

		$path = Yii::getPathOfAlias('application.views.benachrichtigungen') . '/suchergebnisse_email_html.php';
		if (!file_exists($path)) throw new Exception('Template ' . $path . ' does not exist.');
		require($path);
		Yii::app()->end();
	}

}
