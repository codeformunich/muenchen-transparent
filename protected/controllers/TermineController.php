<?php

class TermineController extends RISBaseController
{

	/**
	 * @param int $id
	 */
	public function actionAnzeigen($id) {
		/** @var Termin $antrag */
		$termin = Termin::model()->findByPk($id);
		if (!$termin) {
			$this->render('/index/error', array("code" => 404, "message" => "Der Termin wurde nicht gefunden"));
			return;
		}
		$this->render("anzeige", array(
			"termin" => $termin
		));
	}

}