<?php

namespace app\controllers;

use Yii;
use app\components\AntiXSS;
use app\components\RISBaseController;
use app\components\RISTools;
use app\models\Rechtsdokument;
use app\models\Text;
use yii\helpers\Url;

class InfosController extends RISBaseController
{
    public function actionSoFunktioniertStadtpolitik()
    {
        $this->top_menu = "so_funktioniert";

        /** @var Text $text */
        $text = Text::findOne(25);

        if ($this->binContentAdmin() && AntiXSS::isTokenSet("save")) {
            if (strlen($_REQUEST["text"]) == 0) die("Kein Text angegeben");
            $text->text = $_REQUEST["text"];
            $text->save();
            $this->msg_ok = "Gespeichert.";
        }

        $this->render("stadtpolitik", [
            "text"   => $text,
            "my_url" => Url::to("infos/soFunktioniertStadtpolitik"),
        ]);
    }

    public function actionImpressum()
    {
        $this->top_menu = "impressum";
        $this->std_content_page(23, Url::to("infos/impressum"));
    }

    public function actionDatenschutz()
    {
        $this->top_menu = "datenschutz";
        $this->std_content_page(26, Url::to("infos/datenschutz"));
    }

    public function actionNews()
    {
        $this->top_menu = "";
        $this->std_content_page(28, Url::to("infos/news"));
    }

    public function actionAPI()
    {
        $this->top_menu = "api";
        $this->std_content_page(22, Url::to("infos/api"));
    }

    public function actionUeber()
    {
        $this->top_menu = "";
        $this->std_content_page(21, Url::to("infos/ueber"));
    }

    public function actionStadtrecht()
    {
        $this->top_menu = "so_funktioniert";
        $this->render("stadtrecht");
    }

    public function actionStadtrechtDokument($id)
    {
        /** @var Rechtsdokument $dok */
        $dok = Rechtsdokument::findOne($id);
        if (!$dok) {
            $this->render('../index/error', ["code" => 404, "message" => "Das Dokument wurde nicht gefunden"]);
            Yii::$app->end();
        }
        $this->render("stadtrecht_dokument", ["dokument" => $dok]);
    }

    /**
     * @param int $id
     * @param string $my_url
     * @param bool $show_titles
     * @param bool $insert_tooltips
     */
    public function std_content_page($id, $my_url, $show_title = true, $insert_tooltips = false)
    {
        /** @var Text $text */
        $text = Text::findOne($id);

        if ($this->binContentAdmin() && AntiXSS::isTokenSet("save")) {
            if (strlen($_REQUEST["text"]) == 0) die("Kein Text angegeben");
            $text->text = $_REQUEST["text"];
            $text->save();
            $this->msg_ok = "Gespeichert.";
        }

        $this->render("std", [
            "text"            => $text,
            "my_url"          => $my_url,
            "show_title"      => $show_title,
            "insert_tooltips" => $insert_tooltips,
        ]);
    }


    public function actionFeedback()
    {
        $this->top_menu = "";

        if (AntiXSS::isTokenSet("send")) {
            $fp = fopen(EMAIL_LOG_FILE . "." . date("YmdHis"), "a");
            fwrite($fp, date("Y-m-d H:i:s") . "\n");
            fwrite($fp, print_r($_REQUEST, true));
            fclose($fp);

            $text = "E-Mail: " . $_REQUEST["email"] . "\n";
            $text .= "\n\n";
            $text .= $_REQUEST["message"];

            RISTools::send_email(Yii::$app->params['adminEmail'], "[München Transparent] Feedback", $text, null, "feedback");

            $this->render('feedback_done', []);
        } else {
            $this->render('feedback_form', [
                "current_url" => Url::to("infos/feedback"),
            ]);
        }
    }

    public function actionGlossar()
    {
        $this->top_menu = "so_funktioniert";

        if (AntiXSS::isTokenSet("anlegen") && $this->binContentAdmin()) {
            $text                     = new Text();
            $text->typ                = Text::$TYP_GLOSSAR;
            $text->titel              = $_REQUEST["titel"];
            $text->text               = $_REQUEST["text"];
            $text->pos                = 0;
            $text->edit_datum         = new DbExpression("NOW()");
            $text->edit_benutzerIn_id = $this->aktuelleBenutzerIn()->id;
            $text->save();
        }

        $eintraege = Text::find()->findAllByAttributes([
            "typ" => Text::$TYP_GLOSSAR,
        ], ["order" => "titel"]);

        $this->render('glossar', [
            "eintraege" => $eintraege,
        ]);
    }


    /**
     * @param int $id
     * @throws Exception
     */
    public function actionGlossarBearbeiten($id)
    {
        if (!$this->binContentAdmin()) throw new Exception("Kein Zugriff");

        $this->top_menu = "so_funktioniert";

        /** @var Text $eintrag */
        $eintrag = Text::findOne([
            "id"  => $id,
            "typ" => Text::$TYP_GLOSSAR,
        ]);
        if (!$eintrag) throw new Exception("Nicht gefunden");

        if (AntiXSS::isTokenSet("speichern")) {
            $eintrag->titel              = $_REQUEST["titel"];
            $eintrag->text               = $_REQUEST["text"];
            $eintrag->edit_datum         = new DbExpression("NOW()");
            $eintrag->edit_benutzerIn_id = $this->aktuelleBenutzerIn()->id;
            $eintrag->save();

            $this->redirect(Url::to("infos/glossar"));
        }

        if (AntiXSS::isTokenSet("del")) {
            $eintrag->delete();
            $this->redirect(Url::to("infos/glossar"));
        }

        $this->render('glossar_bearbeiten', [
            "eintrag" => $eintrag,
        ]);
    }
}
