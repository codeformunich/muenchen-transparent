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

		if (AntiXSS::isTokenSet("tag_del") && $this->binContentAdmin()) {
			Yii::app()->db->createCommand()->delete("antraege_tags", "antrag_id=:antrag_id AND tag_id=:tag_id", array(
				"antrag_id" => $antrag->id, "tag_id" => AntiXSS::getTokenVal("tag_del")
			));
		}

		if (AntiXSS::isTokenSet("tag_add") && $this->aktuelleBenutzerIn()) {
			/** @var Tag $tag */
			$tag = Tag::model()->findByAttributes(array("name" => $_REQUEST["tag_name"]));
			if (!$tag) {
				$tag = new Tag();
				$tag->name = $_REQUEST["tag_name"];
				$tag->angelegt_benutzerIn_id = $this->aktuelleBenutzerIn()->id;
				$tag->angelegt_datum = new CDbExpression('NOW()');
				$tag->reviewed = ($this->binContentAdmin() ? 1 : 0);
				$tag->save();

				if (count($tag->getErrors()) > 0) {
					$this->render('/index/error', array("code" => 500, "message" => "Ein Fehler beim Anlegen des Schlagworts trat auf"));
				}
			}

			Yii::app()->db->createCommand()->insert("antraege_tags", array(
				"antrag_id" => $antrag->id, "tag_id" => $tag->id, "zugeordnet_datum" => date("Y-m-d H:i:s"), "zugeordnet_benutzerIn_id" => $this->aktuelleBenutzerIn()->id
			));
		}

		$this->render("anzeige", array(
			"antrag" => $antrag,
		));
	}

}