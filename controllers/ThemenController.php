<?php

namespace app\controllers;

use Yii;
use app\components\AntiXSS;
use app\components\RISBaseController;
use app\models\Antrag;
use app\models\Dokument;
use app\models\Referat;
use app\models\Tag;
use app\models\Text;

class ThemenController extends RISBaseController
{

    public function actionReferat($referat_url)
    {
        /** @var Referat $ref */
        $ref = Referat::findOne(["urlpart" => $referat_url]);
        if (!$ref) die("Nicht gefunden");

        $this->top_menu = "themen";

        $von              = date("Y-m-d H:i:s", time() - 3600 * 24 * 30);
        $bis              = date("Y-m-d H:i:s", time());
        $antraege_referat = Antrag::find()->neueste_stadtratsantragsdokumente_referat($ref->id, $von, $bis)->findAll();

        $text = Text::findOne(["typ" => Text::$TYP_REFERAT, "titel" => $ref->name]);
        $my_url = Url::to("/themen/referat/" . $referat_url);

        if ($this->binContentAdmin() && AntiXSS::isTokenSet("save")) {
            if (strlen($_REQUEST["text"]) == 0) die("Kein Text angegeben");
            $text->text = $_REQUEST["text"];
            $text->save();
            $this->msg_ok = "Gespeichert.";
        }

        return $this->render("referat", [
            "referat"          => $ref,
            "antraege_referat" => $antraege_referat,
            "text"             => $text,
            "my_url"           => $my_url,
        ]);
    }

    /**
     *
     */
    public function actionIndex()
    {
        $this->top_menu = "themen";
        return $this->render("index", [
            "referate"   => Referat::findAll(),
            "highlights" => Dokument::getHighlightDokumente(5),
            "tags"       => Tag::getTopTags(10),
        ]);
    }

    /**
     * @param int $tag_id
     * @param string $tag_name
     */
    public function actionTag($tag_id, $tag_name = "")
    {
        $tag_id = IntVal($tag_id);

        $this->top_menu = "themen";

        /** @var Tag $tag */
        $tag = Tag::findOne($tag_id);

        $antraege_tag = $tag->antraege;

        return $this->render("tag", [
            "tag"          => $tag,
            "antraege_tag" => $antraege_tag,
        ]);
    }


}
