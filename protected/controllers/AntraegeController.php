<?php

class AntraegeController extends RISBaseController
{

	/**
	 * @param int $id
	 */
	public function actionAnzeigen($id) {
		/** @var Antrag $antrag */
		$antrag = Antrag::model()->findByPk($id);
		if (!$antrag) {
			$this->render('/index/error', array("code" => 404, "message" => "Der Antrag wurde nicht gefunden"));
			return;
		}
		$this->render("anzeige", array(
			"antrag" => $antrag
		));
	}

}