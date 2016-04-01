<?php

class PersonenController extends RISBaseController
{

    /**
     * @param null|int $ba
     */
    public function actionIndex($ba = null)
    {
        $this->top_menu = "personen";

        $this->render('stat', [
            "personen"     => StadtraetIn::getByFraktion(date("Y-m-d"), $ba),
            "personen_typ" => ($ba > 0 ? "ba" : "str"),
            "ba_nr"        => $ba
        ]);
    }

    /**
     * @param int $id
     */
    public function actionPerson($id)
    {
        $this->top_menu = "personen";

        /** @var StadtraetIn $person */
        $person = StadtraetIn::model()->findByPk($id);

        $this->render("person", [
            "person" => $person,
        ]);
    }

    /**
     * @param int $id
     */
    public function actionPersonAlt($id)
    {
        /** @var StadtraetIn $person */
        $person = StadtraetIn::model()->findByPk($id);
        $this->redirect($person->getLink());
    }



    /**
     * @param int $id
     */
    public function actionPersonBearbeiten($id)
    {
        $this->top_menu = "personen";

        $ich = $this->aktuelleBenutzerIn();
        if (!$ich) $this->errorMessageAndDie(403, "Du musst eingeloggt sein, um deinen Eintrag zu bearbeiten.");

        /** @var StadtraetIn $person */
        $person = StadtraetIn::model()->findByPk($id);
        if ($person->benutzerIn_id != $ich->id) $this->errorMessageAndDie(403, "Du kannst nur deinen eigenen Eintrag bearbeiten.");

        if (AntiXSS::isTokenSet("save")) {
            $person->web          = $_REQUEST["web"];
            $person->twitter      = trim($_REQUEST["twitter"], "\t\n\r@");
            $person->facebook     = preg_replace("/^https?:\/\/(www\.)?facebook\.com\//siu", "", $_REQUEST["facebook"]);
            $person->email        = $_REQUEST["email"];
            $person->beschreibung = trim($_REQUEST["beschreibung"]);
            $person->quellen      = "Selbstauskunft";
            $x                    = explode(".", $_REQUEST["geburtstag"]);
            if (count($x) == 3) {
                $person->geburtstag = $x[2] . "-" . $x[1] . "-" . $x[0];
            } else {
                if ($x[0] > 1900) $person->geburtstag = $x[0] . "-00:00";
                else $person->geburtstag = null;
            }
            $person->save();
            $this->msg_ok = "Gespeichert";
        }

        $this->render("person-bearbeiten", [
            "person" => $person,
        ]);
    }


    /**
     * @param int $id
     */
    public function actionBinIch($id)
    {
        $this->top_menu = "personen";

        $this->requireLogin($this->createUrl("personen/binIch", ["id" => $id]));

        /** @var StadtraetIn $person */
        $person = StadtraetIn::model()->findByPk($id);
        if ($person->benutzerIn_id !== null) $this->errorMessageAndDie(403, "Diese Person ist schon einem Account zugeordnet. Falls das ein Fehler ist, schreiben Sie uns bitte per Mail (" . Yii::app()->params["adminEmail"] . ")");

        $this->render("person-binich", [
            "person" => $person,
        ]);
    }


}
