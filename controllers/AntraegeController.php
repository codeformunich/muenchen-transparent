<?php

class AntraegeController extends RISBaseController
{

    public function actionAjaxTagsSuggest($term)
    {
        $sqlterm = addslashes($term);
        $found   = Yii::$app->db->createCommand("SELECT id, name, LOCATE(\"$sqlterm\", name) firstpos FROM tags WHERE name LIKE \"%$sqlterm%\" ORDER BY firstpos, LENGTH(name), name LIMIT 0,10")->queryAll();
        $tags    = [];
        foreach ($found as $f) $tags[] = ["text" => $f["name"], "value" => $f["name"]];
        header('Content-type: application/json; charset=UTF-8');
        echo json_encode($tags);
    }

    /**
     * @param int $id
     */
    public function actionAjaxThemenverwandte($id)
    {
        /** @var Antrag $antrag */
        $antrag = Antrag::model()->findByPk($id);
        if (!$antrag) {
            $this->render('/index/error', ["code" => 404, "message" => "Der Antrag wurde nicht gefunden"]);
            return;
        }

        $this->render("related_list", [
            "related" => $antrag->errateThemenverwandteAntraege(10),
        ]);
    }

    /**
     * @param int $id
     */
    public function actionThemenverwandte($id)
    {
        /** @var Antrag $antrag */
        $antrag = Antrag::model()->findByPk($id);
        if (!$antrag) {
            $this->render('/index/error', ["code" => 404, "message" => "Der Antrag wurde nicht gefunden"]);
            return;
        }
        $this->render("themenverwandte", [
            "antrag" => $antrag,
        ]);
    }

    /**
     * @param int $id
     */
    public function actionAnzeigen($id)
    {
        $this->performLoginActions();

        /** @var Antrag $antrag */
        $antrag = Antrag::model()->findByPk($id);
        if (!$antrag) {
            $this->render('/index/error', ["code" => 404, "message" => "Der Antrag wurde nicht gefunden"]);
            return;
        }

        if (AntiXSS::isTokenSet("abonnieren")) {
            $this->requireLogin($this->createUrl("antraege/anzeigen", ["id" => $id]));
            $antrag->getVorgang()->abonnieren($this->aktuelleBenutzerIn());
        }

        if (AntiXSS::isTokenSet("deabonnieren")) {
            $this->requireLogin($this->createUrl("antraege/anzeigen", ["id" => $id]));
            $antrag->getVorgang()->deabonnieren($this->aktuelleBenutzerIn());
        }

        if (AntiXSS::isTokenSet("tag_add") && $this->aktuelleBenutzerIn()) {
            $tags = explode(",", $_REQUEST["tags_neu"]);

            foreach ($tags as $tag_name) if (mb_strlen(trim($tag_name)) > 0) try {
                $tag_name = trim($tag_name);
                $tag      = Tag::model()->findByAttributes(["name" => $tag_name]);
                if (!$tag) {
                    $tag                         = new Tag();
                    $tag->name                   = $tag_name;
                    $tag->angelegt_benutzerIn_id = $this->aktuelleBenutzerIn()->id;
                    $tag->angelegt_datum         = new DbExpression('NOW()');
                    $tag->reviewed               = ($this->binContentAdmin() ? 1 : 0);
                    $tag->save();

                    if (count($tag->getErrors()) > 0) {
                        $this->render('/index/error', ["code" => 500, "message" => "Ein Fehler beim Anlegen des Schlagworts trat auf"]);
                    }
                }

                Yii::$app->db->createCommand()->insert("antraege_tags", [
                    "antrag_id" => $antrag->id, "tag_id" => $tag->id, "zugeordnet_datum" => date("Y-m-d H:i:s"), "zugeordnet_benutzerIn_id" => $this->aktuelleBenutzerIn()->id
                ]);
            } catch (Exception $e) {
                // Sind hauptsächlich doppelte Einträge -> ignorieren
            }
        }

        $this->render("anzeige", [
            "antrag"   => $antrag,
            "tag_mode" => (isset($_REQUEST["tag_mode"]) && $_REQUEST["tag_mode"] == "1"),
        ]);
    }

}
