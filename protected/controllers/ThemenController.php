<?php

class ThemenController extends RISBaseController
{

	public function actionReferat($referat_url)
	{
		/** @var Referat $ref */
		$ref = Referat::model()->findByAttributes(array("urlpart" => $referat_url));
		if (!$ref) die("Nicht gefunden");

		$von = date("Y-m-d H:i:s", time() - 3600 * 24 * 30);
		$bis = date("Y-m-d H:i:s", time());
		$antraege_referat = Antrag::model()->neueste_stadtratsantragsdokumente_referat($ref->id, $von, $bis)->findAll();

		$this->render("referat", array(
			"referat"          => $ref,
			"antraege_referat" => $antraege_referat,
		));
	}

	/**
	 *
	 */
	public function actionIndex()
	{
		$this->top_menu = "themen";
		$this->render("index", array(
			"referate"   => Referat::model()->findAll(),
			"highlights" => AntragDokument::getHighlightDokumente(5),
		));
	}


}