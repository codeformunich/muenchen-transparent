<?php

class AdminController extends RISBaseController
{


	public function actionStadtraetInnenPersonen() {

		$this->top_menu = "admin";

		$msg_ok = null;
		if (isset($_REQUEST["save"])) {
			/** @var Person $person */
			$person = Person::model()->findByPk($_REQUEST["person"]);
			if ($person) {
				if (isset($_REQUEST["fraktion"])) {
					$person->typ = Person::$TYP_FRAKTION;
					$person->ris_stadtraetIn = null;
				}
				else {
					$person->typ = Person::$TYP_PERSON;
					$person->ris_stadtraetIn = (isset($_REQUEST["stadtraetIn"]) ? $_REQUEST["stadtraetIn"] : null);
				}
				$person->save();
			}
			$msg_ok = "Gespeichert";
		}

		/** @var Person[] $personen */
		$personen = Person::model()->findAll(array("order" => "name"));

		/** @var StadtraetIn[] $stadtraetInnen */
		$stadtraetInnen = StadtraetIn::model()->findAll(array("order" => "name"));

		$this->render("stadtraetInnenPersonen", array(
			"msg_ok" => $msg_ok,
			"personen" => $personen,
			"stadtraetInnen" => $stadtraetInnen,
		));
	}


	public function actionIndex() {
		$this->top_menu = "admin";

		$this->render("index");
	}

}