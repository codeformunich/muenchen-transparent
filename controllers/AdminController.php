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
        $personen = Person::model()->findAll(["order" => "name"]);

        /** @var StadtraetIn[] $stadtraetInnen */
        $stadtraetInnen = StadtraetIn::model()->findAll(["order" => "name"]);

        $this->render("stadtraetInnenPersonen", [
            "personen"       => $personen,
            "stadtraetInnen" => $stadtraetInnen,
        ]);
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

        $this->render("stadtraetInnenSocialMedia", [
            "fraktionen" => $fraktionen,
        ]);
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

        $this->render("stadtraetInnenBeschreibungen", [
            "fraktionen" => $fraktionen,
        ]);
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

        $this->render("stadtraetInnenBenutzerInnen", [
            "stadtraetInnen" => $stadtraetInnen,
        ]);
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
            $tag_alt     = Tag::model()->findByAttributes(["id" => $_REQUEST["tag_id"]]);
            $gleichnamig = Tag::model()->findByAttributes(["name" => $_REQUEST["neuer_name"]]);

            if ($gleichnamig != null) { // Tag mit neuem Namen existiert bereits-> merge
                // Zuerst bei allen Anträgen mit beiden tags den Eintrag für den alten tag löschen
                Yii::app()->db->createCommand('DELETE t1 FROM antraege_tags t1 INNER JOIN antraege_tags t2 ON t1.antrag_id=t2.antrag_id WHERE t1.tag_id=:tag_id_alt AND t2.tag_id=:tag_id_neu')
                    ->bindValues([':tag_id_neu' => $gleichnamig->id, ':tag_id_alt' => $tag_alt->id])
                    ->execute();

                Yii::app()->db->createCommand('UPDATE antraege_tags SET tag_id=:tag_id_neu WHERE tag_id=:tag_id_alt')
                    ->bindValues([':tag_id_neu' => $gleichnamig->id, ':tag_id_alt' => $tag_alt->id])
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
            $tag = Tag::model()->findByAttributes(["id" => $_REQUEST["tag_id"]]);

            Yii::app()->db->createCommand('DELETE FROM antraege_tags WHERE tag_id=:tag_id')
                ->bindValues([':tag_id' => $tag->id])
                ->execute();

            $tag->delete();

            $this->msg_ok = "Tag gelöscht";
        }

        if (AntiXSS::isTokenSet("einzelnen_tag_loeschen")) {
            $tag = Tag::model()->findByAttributes(["name" => $_REQUEST["tag_name"]]);

            Yii::app()->db->createCommand('DELETE FROM antraege_tags WHERE tag_id=:tag_id AND antrag_id=:antrag_id')
                ->bindValues([':tag_id' => $tag->id, ':antrag_id' => $_REQUEST["antrag_id"]])
                ->execute();

            $this->msg_ok = "Einzelner Tag gelöscht";
        }

        $tags = Tag::model()->findAll();
        usort($tags, function ($tag1, $tag2) {
            /**
            * @var Tag $dok1
            * @var Tag $dok2
            */
            $name1 = strtolower($tag1->name);
            $name2 = strtolower($tag2->name);
            if ($name1 == $name2) {
                return 0;
            }
            return ($name1 > $name2) ? +1 : -1;
        });

        $this->render("tags", [
            "tags" => $tags,
        ]);
    }

    public function actionBuergerInnenversammlungen()
    {
        if (!$this->binContentAdmin()) $this->errorMessageAndDie(403, "");

        $this->top_menu = "admin";

        if (AntiXSS::isTokenSet("delete")) {
            /** @var Termin $termin */
            $termin = Termin::model()->findByPk(AntiXSS::getTokenVal("delete"));
            $termin->delete();
            $this->msg_ok = "Gelöscht.";
        }

        if (AntiXSS::isTokenSet("save")) {
            if (isset($_REQUEST["neu"]) && $_REQUEST["neu"]["datum"] != "" && $_REQUEST["neu"]["ba_nr"] > 0) {
                $result = Yii::app()->db->createCommand("SELECT MIN(id) minid FROM termine")->queryAll();
                $id     = $result[0]["minid"];
                if ($id >= 0) $id = 0;
                $id--;

                $termin                         = new Termin();
                $termin->id                     = $id;
                $termin->ba_nr                  = IntVal($_REQUEST["neu"]["ba_nr"]);
                $termin->typ                    = Termin::$TYP_BUERGERVERSAMMLUNG;
                $termin->sitzungsort            = $_REQUEST["neu"]["ort"];
                $termin->termin                 = $_REQUEST["neu"]["datum"];
                $termin->datum_letzte_aenderung = new CDbExpression('NOW()');
                if (!$termin->save()) {
                    $this->msg_err = print_r($termin->getErrors(), true);
                }
            }
            if (isset($_REQUEST["termin"])) foreach ($_REQUEST["termin"] as $id => $save) {
                /** @var Termin $termin */
                $termin              = Termin::model()->findByPk($id);
                $termin->sitzungsort = $save["ort"];
                $termin->termin      = $save["datum"];
                $termin->save();
            }
            $this->msg_ok = "Gespeichert";
        }

        $termine = Termin::model()->findAllByAttributes(["typ" => Termin::$TYP_BUERGERVERSAMMLUNG], ["order" => "termin DESC"]);
        $this->render("buergerInnenversammlungen", [
            "termine" => $termine,
        ]);
    }
}
