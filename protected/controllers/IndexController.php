<?php

class IndexController extends RISBaseController
{

	public function actionFeed() {
		/** @var array|RISAenderung[] $aenderungen */
		$aenderungen = RISAenderung::model()->findAll(array("order" => "id DESC", "limit" => 100));
		$data = array();
		foreach ($aenderungen as $aenderung) $data[] = $aenderung->toFeedData();

		$this->render("feed", array(
			"feed_title" => "OpenRIS Änderungen",
			"feed_description" => "OpenRIS Änderungen",
			"data" => $data,
		));
	}

	public function actionSuche() {

		$suchbegriff = $_POST["suchbegriff"];
		$ergebnisse = AntragDokument::volltextsuche($suchbegriff);

		$this->render("suchergebnisse", array(
			"suchbegriff" => $suchbegriff,
			"ergebnisse" => $ergebnisse
		));
	}

	public function actionDokument($id) {
		/** @var AntragDokument $dokument */
		$dokument = AntragDokument::model()->findByPk($id);
		$morelikethis = $dokument->solrMoreLikeThis();
		$this->render("dokument_intern", array(
			"dokument" => $dokument,
			"morelikethis" => $morelikethis,
		));
	}

	public function actionStadtrat() {
		echo "Stadtrat";
	}

	public function actionBa($ba_nr) {
		echo "BA $ba_nr";
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		} else {
			$this->render('error', array("code" => 400, "message" => "Ein Fehler ist aufgetreten"));
		}
	}

}