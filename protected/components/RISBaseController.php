<?php

class RISBaseController extends CController
{
    /**
     * Alternative: //layouts/width_wide
     */
    public $layout = '//layouts/width_std';

    /**
     * @var string
     */
    public $html_description = "";
    public $html_itemprop = "";

    /**
     * @var string
     */
    public $inline_css = "";


    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = [];
    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs = [];

    public $top_menu = "";

    // Options to load js and css libraries. The files are then loaded in views/layouts/main.php
    public $load_calendar     = false;
    public $load_ckeditor     = false;
    public $load_isotope_js   = false;
    public $load_leaflet      = false;
    public $load_list_js      = false;
    public $load_mediaelement = false;
    public $load_pdf_js       = false;
    public $load_selectize_js = false;
    public $load_shariff      = false;

    public $suche_pre  = "";

    public $msg_ok = "";
    public $msg_err = "";

    protected function performLoginActions($code = "")
    {
        /** @var CWebUser $user */
        $user = Yii::app()->getUser();

        $this->msg_err = "";
        $this->msg_ok  = "";

        if ($code != "") {
            $x = explode("-", $code);
            /** @var BenutzerIn $benutzerIn */
            $benutzerIn = BenutzerIn::model()->findByPk($x[0]);
            if (!$benutzerIn) $this->msg_err = "Diese Seite existiert nicht. Vielleicht wurde der Bestätigungslink falsch kopiert?";
            elseif ($benutzerIn->email_bestaetigt) $this->msg_err = "Dieser Zugang wurde bereits bestätigt.";
            elseif (!$benutzerIn->emailBestaetigen($code)) $this->msg_err = "Diese Seite existiert nicht. Vielleicht wurde der Bestätigungslink falsch kopiert? (Beachte, dass der Link in der E-Mail nur 2-3 Tage lang gültig ist.";
            else {
                $this->msg_ok   = "Der Zugang wurde bestätigt. Ab jetzt erhältst du Benachrichtigungen per E-Mail, wenn du das so eingestellt hast.";
                $identity = new RISUserIdentity($benutzerIn);
                Yii::app()->user->login($identity);
            }
        }


        if (AntiXSS::isTokenSet("abmelden") && !$user->isGuest) {
            $user->logout();
        }

        if (AntiXSS::isTokenSet("login_anlegen") && $user->isGuest && !isset($_REQUEST["register"])) {
            /** @var BenutzerIn $benutzerIn */
            $benutzerIn = BenutzerIn::model()->findByAttributes(["email" => $_REQUEST["email"]]);
            if ($benutzerIn) {
                if ($benutzerIn->validate_password($_REQUEST["password"])) {
                    $identity = new RISUserIdentity($benutzerIn);
                    Yii::app()->user->login($identity);
                } else {
                    $this->msg_err = "Das angegebene Passwort ist falsch.";
                }
            } else {
                $this->msg_err = "Für die angegebene E-Mail-Adresse existiert noch kein Zugang.";
            }
        }

        if (AntiXSS::isTokenSet("login_anlegen") && $user->isGuest && isset($_REQUEST["register"])) {
            /** @var BenutzerIn[] $gefundene_benutzerInnen */
            $gefundene_benutzerInnen = BenutzerIn::model()->findAll([
                "condition" => "email='" . addslashes($_REQUEST["email"]) . "'"
            ]);
            if (count($gefundene_benutzerInnen) > 0) {
                $this->msg_err = "Es existiert bereits ein Zugang für diese E-Mail-Adresse";
            } elseif (trim($_REQUEST["password"]) == "") {
                $this->msg_err = "Bitte gib ein Passwort an";
            } elseif ($_REQUEST["password"] != $_REQUEST["password2"]) {
                $this->msg_err = "Die beiden angegebenen Passwörter stimmen nicht überein.";
            } else {

                $benutzerIn = BenutzerIn::createBenutzerIn(trim($_REQUEST["email"]), $_REQUEST["password"]);
                if ($benutzerIn->save()) {
                    $benutzerIn->sendEmailBestaetigungsMail();
                    $identity = new RISUserIdentity($benutzerIn);
                    Yii::app()->user->login($identity);

                    $this->msg_ok = "Der Zugang wurde angelegt. Es wurde eine Bestätigungs-Mail an die angegebene Adresse geschickt. Bitte klicke auf den Link in dieser Mail an, um E-Mail-Benachrichtigungen zu erhalten.";
                } else {
                    $this->msg_err = "Leider ist ein (ungewöhnlicher) Fehler aufgetreten.";
                    $errs    = $benutzerIn->getErrors();
                    foreach ($errs as $err) foreach ($err as $e) $this->msg_err .= $e;
                }
            }
        }
    }

    /**
     * @param string $target_url
     * @param string $code
     * @return array
     */
    protected function requireLogin($target_url, $code = "")
    {
        $this->performLoginActions($code);

        if (Yii::app()->getUser()->isGuest) {
            $this->render("../index/login", [
                "current_url" => $target_url,
            ]);
            Yii::app()->end();
        } else {
            $benutzerIn = $this->aktuelleBenutzerIn();
            if (!$benutzerIn) {
                Yii::app()->getUser()->logout();
                $this->redirect("/");
            }
        }
    }

    /**
     * @return BenutzerIn|null
     */
    public function aktuelleBenutzerIn()
    {
        $user = Yii::app()->getUser();
        if ($user->isGuest) return null;
        /** @var BenutzerIn $ich */
        $ich = BenutzerIn::model()->findByAttributes(["email" => Yii::app()->user->id]);
        return $ich;
    }

    /**
     * @return bool
     */
    public function binContentAdmin()
    {
        $curr = $this->aktuelleBenutzerIn();
        if ($curr === null) return false;
        return $curr->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_CONTENT);
    }

    /**
     * @param int $error_code
     * @param string $error_message
     */
    public function errorMessageAndDie($error_code, $error_message)
    {
        $this->render("../index/error", [
            "code"    => $error_code,
            "message" => $error_message,
        ]);
        Yii::app()->end($error_code);
        die();
    }
}
