<?php

class AdminController extends RISBaseController
{


	public function actionStadtraetInnenPersonen() {
		if (!$this->binContentAdmin()) $this->errorMessageAndDie(403, "");

		$this->top_menu = "admin";

		$msg_ok = null;
		if (AntiXSS::isTokenSet("save")) {
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



	public function actionStadtraetInnenSocialMedia() {
		if (!$this->binContentAdmin()) $this->errorMessageAndDie(403, "");

		$this->top_menu = "admin";

		$msg_ok = null;
		if (AntiXSS::isTokenSet("save") && isset($_REQUEST["twitter"])) {
			foreach($_REQUEST["twitter"] as $str_id => $twitter) {
				/** @var StadtraetIn $str */
				$str = StadtraetIn::model()->findByPk($str_id);
				$str->twitter = (trim($twitter) == "" ? null : trim($twitter));
				$str->facebook = (trim($_REQUEST["facebook"][$str_id]) == "" ? null : trim($_REQUEST["facebook"][$str_id]));
				$str->abgeordnetenwatch = (trim($_REQUEST["abgeordnetenwatch"][$str_id]) == "" ? null : trim($_REQUEST["abgeordnetenwatch"][$str_id]));
				$str->web = (trim($_REQUEST["web"][$str_id]) == "" ? null : trim($_REQUEST["web"][$str_id]));
				$str->save();
			}
			$msg_ok = "Gespeichert";
		}

		/** @var array[] $fraktionen */
		$fraktionen = StadtraetIn::getGroupedByFraktion(date("Y-m-d"), null);

		$this->render("stadtraetInnenSocialMedia", array(
			"msg_ok" => $msg_ok,
			"fraktionen" => $fraktionen,
		));
	}


	public function actionIndex() {
		if (!$this->binContentAdmin()) $this->errorMessageAndDie(403, "");

		$this->top_menu = "admin";

		$this->render("index");
	}

}