<?php

class AdminController extends RISBaseController
{
    public function actionStadtraetInnenPersonen()
    {
        if (!$this->binContentAdmin()) $this->errorMessageAndDie(403, "");

        $this->top_menu = "admin";

        if (AntiXSS::isTokenSet("save")) {
            /** @var Person $person */
            $person = Person::model()->findByPk($_REQUEST["person"]);
            if ($person) {
                if (isset($_REQUEST["fraktion"])) {
                    $person->typ             = Person::$TYP_FRAKTION;
                    $person->ris_stadtraetIn = null;
                } else {
                    $person->typ             = Person::$TYP_PERSON;
                    $person->ris_stadtraetIn = (isset($_REQUEST["stadtraetIn"]) ? $_REQUEST["stadtraetIn"] : null);
                }
                $person->save();
            }
            $this->msg_ok = "Gespeichert";
        }

        /** @var Person[] $personen */
        $personen = Person::model()->findAll(array("order" => "name"));

        /** @var StadtraetIn[] $stadtraetInnen */
        $stadtraetInnen = StadtraetIn::model()->findAll(array("order" => "name"));

        $this->render("stadtraetInnenPersonen", array(
            "personen"       => $personen,
            "stadtraetInnen" => $stadtraetInnen,
        ));
    }


    public function actionStadtraetInnenSocialMedia()
    {
        if (!$this->binContentAdmin()) $this->errorMessageAndDie(403, "");

        $this->top_menu = "admin";

        if (AntiXSS::isTokenSet("save") && isset($_REQUEST["twitter"])) {
            foreach ($_REQUEST["twitter"] as $str_id => $twitter) {
                /** @var StadtraetIn $str */
                $str                    = StadtraetIn::model()->findByPk($str_id);
                $str->twitter           = (trim($twitter) == "" ? null : trim($twitter));
                $str->facebook          = (trim($_REQUEST["facebook"][$str_id]) == "" ? null : trim($_REQUEST["facebook"][$str_id]));
                $str->abgeordnetenwatch = (trim($_REQUEST["abgeordnetenwatch"][$str_id]) == "" ? null : trim($_REQUEST["abgeordnetenwatch"][$str_id]));
                $str->web               = (trim($_REQUEST["web"][$str_id]) == "" ? null : trim($_REQUEST["web"][$str_id]));
                $str->save();
            }
            $this->msg_ok = "Gespeichert";
        }

        /** @var array[] $fraktionen */
        $fraktionen = StadtraetIn::getGroupedByFraktion(date("Y-m-d"), null);

        $this->render("stadtraetInnenSocialMedia", array(
            "fraktionen" => $fraktionen,
        ));
    }


    public function actionStadtraetInnenBeschreibungen()
    {
        if (!$this->binContentAdmin()) $this->errorMessageAndDie(403, "");

        $this->top_menu = "admin";
        if (AntiXSS::isTokenSet("save") && isset($_REQUEST["geburtstag"])) {

            foreach ($_REQUEST["geburtstag"] as $str_id => $geburtstag) {
                /** @var StadtraetIn $str */
                $str               = StadtraetIn::model()->findByPk($str_id);
                $str->geburtstag   = ($geburtstag != "" ? $geburtstag : null);
                $str->beschreibung = $_REQUEST["beschreibung"][$str_id];
                $str->quellen      = $_REQUEST["quellen"][$str_id];
                $str->geschlecht   = (isset($_REQUEST["geschlecht"][$str_id]) ? $_REQUEST["geschlecht"][$str_id] : null);
                $str->save();
            }
            $this->msg_ok = "Gespeichert";
        }

        /** @var array[] $fraktionen */
        $fraktionen = StadtraetIn::getGroupedByFraktion(date("Y-m-d"), null);

        $this->render("stadtraetInnenBeschreibungen", array(
            "fraktionen" => $fraktionen,
        ));
    }

    public function actionStadtraetInnenBenutzerInnen()
    {
        $ich = $this->aktuelleBenutzerIn();
        if (!$ich) $this->errorMessageAndDie(403, "");
        if (!$ich->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_USER)) $this->errorMessageAndDie(403, "");

        $this->top_menu = "admin";

        if (AntiXSS::isTokenSet("save") && isset($_REQUEST["BenutzerIn"])) {
            foreach ($_REQUEST["BenutzerIn"] as $strIn_id => $benutzerIn_id) {
                /** @var StadtraetIn $strIn */
                $strIn = StadtraetIn::model()->findByPk($strIn_id);
                if ($benutzerIn_id > 0) $strIn->benutzerIn_id = IntVal($benutzerIn_id);
                else $strIn->benutzerIn_id = null;
                $strIn->save();
            }
            $this->msg_ok = "Gespeichert";
        }

        /** @var StadtraetIn[] $stadtraetInnen */
        $stadtraetInnen = StadtraetIn::model()->findAll();
        $stadtraetInnen = StadtraetIn::sortByName($stadtraetInnen);

        $this->render("stadtraetInnenBenutzerInnen", array(
            "stadtraetInnen" => $stadtraetInnen,
        ));
    }


    public function actionIndex()
    {
        if (!$this->binContentAdmin()) $this->errorMessageAndDie(403, "");

        $this->top_menu = "admin";

        $this->render("index");
    }

    public function actionTags()
    {
        $ich = $this->aktuelleBenutzerIn();
        if (!$ich || !$ich->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_TAG)) $this->errorMessageAndDie(403, "");

        $this->top_menu = "admin";

        if (AntiXSS::isTokenSet("tag_umbennen")) {
            $tag_alt = Tag::model()->findByAttributes(array("id" => $_REQUEST["tag_id"]));
            $gleichnamig = Tag::model()->findByAttributes(array("name" => $_REQUEST["neuer_name"]));

            if($gleichnamig != null) { // Tag mit neuem Namen existiert bereits-> merge
                // Zuerst bei allen Anträgen mit beiden tags den Eintrag für den alten tag löschen
                Yii::app()->db->createCommand('DELETE t1 FROM antraege_tags t1 INNER JOIN antraege_tags t2 ON t1.antrag_id=t2.antrag_id WHERE t1.tag_id=:tag_id_alt AND t2.tag_id=:tag_id_neu')
                              ->bindValues(array(':tag_id_neu' => $gleichnamig->id, ':tag_id_alt' => $tag_alt->id))
                              ->execute();

                Yii::app()->db->createCommand('UPDATE antraege_tags SET tag_id=:tag_id_neu WHERE tag_id=:tag_id_alt')
                              ->bindValues(array(':tag_id_neu' => $gleichnamig->id, ':tag_id_alt' => $tag_alt->id))
                              ->execute();

                $tag_alt->delete();

                $this->msg_ok = "Tags zusammengeführt";
            } else {
                $tag_alt->name = $_REQUEST["neuer_name"];
                $tag_alt->save();

                $this->msg_ok = "Tag umbenannt";
            }
        }

        if (AntiXSS::isTokenSet("tag_loeschen")) {
            $tag = Tag::model()->findByAttributes(array("id" => $_REQUEST["tag_id"]));

            Yii::app()->db->createCommand('DELETE FROM antraege_tags WHERE tag_id=:tag_id')
                          ->bindValues(array(':tag_id' => $tag->id))
                          ->execute();

            $tag->delete();

            $this->msg_ok = "Tag gelöscht";
        }

        $this->render("tags");
    }
}
