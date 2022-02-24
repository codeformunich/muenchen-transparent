<?php

class InfosController extends RISBaseController
{
    public function actionSoFunktioniertStadtpolitik()
    {
        $this->top_menu = "so_funktioniert";

        /** @var Text $text */
        $text = Text::model()->findByPk(25);

        if ($this->binContentAdmin() && AntiXSS::isTokenSet("save")) {
            if (strlen($_REQUEST["text"]) == 0) die("Kein Text angegeben");
            $text->text = $_REQUEST["text"];
            $text->save();
            $this->msg_ok = "Gespeichert.";
        }

        $this->render("stadtpolitik", [
            "text"   => $text,
            "my_url" => $this->createUrl("infos/soFunktioniertStadtpolitik"),
        ]);
    }

    public function actionImpressum()
    {
        $this->top_menu = "impressum";
        $this->std_content_page(23, $this->createUrl("infos/impressum"));
    }

    public function actionDatenschutz()
    {
        $this->top_menu = "datenschutz";
        $this->std_content_page(26, $this->createUrl("infos/datenschutz"));
    }

    public function actionNews()
    {
        $this->top_menu = "";
        $this->std_content_page(28, $this->createUrl("infos/news"));
    }

    public function actionAPI()
    {
        $this->top_menu = "api";
        $this->std_content_page(22, $this->createUrl("infos/api"));
    }

    public function actionUeber()
    {
        $this->top_menu = "";
        $this->std_content_page(21, $this->createUrl("infos/ueber"));
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
        $text = Text::model()->findByPk($id);

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

            RISTools::send_email(Yii::app()->params['adminEmail'], "[München Transparent] Feedback", $text, null, "feedback");

            $this->render('feedback_done', []);
        } else {
            $this->render('feedback_form', [
                "current_url" => Yii::app()->createUrl("infos/feedback"),
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
            $text->edit_datum         = new CDbExpression("NOW()");
            $text->edit_benutzerIn_id = $this->aktuelleBenutzerIn()->id;
            $text->save();
        }

        $eintraege = Text::model()->findAllByAttributes([
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
        $eintrag = Text::model()->findByAttributes([
            "id"  => $id,
            "typ" => Text::$TYP_GLOSSAR,
        ]);
        if (!$eintrag) throw new Exception("Nicht gefunden");

        if (AntiXSS::isTokenSet("speichern")) {
            $eintrag->titel              = $_REQUEST["titel"];
            $eintrag->text               = $_REQUEST["text"];
            $eintrag->edit_datum         = new CDbExpression("NOW()");
            $eintrag->edit_benutzerIn_id = $this->aktuelleBenutzerIn()->id;
            $eintrag->save();

            $this->redirect($this->createUrl("infos/glossar"));
        }

        if (AntiXSS::isTokenSet("del")) {
            $eintrag->delete();
            $this->redirect($this->createUrl("infos/glossar"));
        }

        $this->render('glossar_bearbeiten', [
            "eintrag" => $eintrag,
        ]);
    }
}
