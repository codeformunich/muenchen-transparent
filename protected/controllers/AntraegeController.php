<?php

class AntraegeController extends RISBaseController
{

	/**
	 * @param int $id
	 */
	public function actionAjaxThemenverwandte($id)
	{
		/** @var Antrag $antrag */
		$antrag = Antrag::model()->findByPk($id);
		if (!$antrag) {
			$this->render('/index/error', array("code" => 404, "message" => "Der Antrag wurde nicht gefunden"));
			return;
		}

		$this->render("related_list", array(
			"related" => $antrag->errateThemenverwandteAntraege(10),
		));
	}

	/**
	 * @param int $id
	 */
	public function actionThemenverwandte($id)
	{
		/** @var Antrag $antrag */
		$antrag = Antrag::model()->findByPk($id);
		if (!$antrag) {
			$this->render('/index/error', array("code" => 404, "message" => "Der Antrag wurde nicht gefunden"));
			return;
		}
		$this->render("themenverwandte", array(
			"antrag" => $antrag,
		));
	}

	/**
	 * @param int $id
	 */
	public function actionAnzeigen($id)
	{
		/** @var Antrag $antrag */
		$antrag = Antrag::model()->findByPk($id);
		if (!$antrag) {
			$this->render('/index/error', array("code" => 404, "message" => "Der Antrag wurde nicht gefunden"));
			return;
		}

		if (AntiXSS::isTokenSet("abonnieren")) {
			$this->requireLogin($this->createUrl("antraege/anzeigen", array("id" => $id)));
			$antrag->vorgang->abonnieren($this->aktuelleBenutzerIn());
		}

		if (AntiXSS::isTokenSet("deabonnieren")) {
			$this->requireLogin($this->createUrl("antraege/anzeigen", array("id" => $id)));
			$antrag->vorgang->deabonnieren($this->aktuelleBenutzerIn());
		}

		$this->render("anzeige", array(
			"antrag" => $antrag,
		));
	}

}