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

    /**
     * @var string
     */
    public $inline_css = "";


    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();
    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs = array();

    public $top_menu = "";

    public $load_pdf_js           = false;
    public $load_leaflet_css      = false;
    public $load_leaflet_draw_css = false;
    public $load_calendar         = false;
    public $load_selectize_js     = false;
    public $load_mediaelement     = false;
    public $suche_pre             = "";

    public $msg_ok                = "";
    public $msg_err               = "";

    private $_assetsBase = null;

    public function getAssetsBase()
    {
        if ($this->_assetsBase === null) {
            /** @var CWebApplication $app */
            $app               = Yii::app();
            $this->_assetsBase = $app->assetManager->publish(
                Yii::getPathOfAlias('application.assets'),
                false,
                -1,
                defined('YII_DEBUG') && YII_DEBUG
            );


            $path = getcwd() . $this->_assetsBase . "/";
            if (!file_exists($path . "bas.js")) {
                $BAfeatures = array();
                /** @var array|Bezirksausschuss[] $BAs */
                $BAs = Bezirksausschuss::model()->findAll();
                foreach ($BAs as $ba) $BAfeatures[] = $ba->toGeoJSONArray();

                file_put_contents($path . "ba_features.js", "BA_FEATURES = " . json_encode($BAfeatures) . ";");
            };
        }
        return $this->_assetsBase;
    }

    protected function performLoginActions($code = "")
    {
        /** @var CWebUser $user */
        $user = Yii::app()->getUser();

        $msg_err = "";
        $msg_ok  = "";

        if ($code != "") {
            $x = explode("-", $code);
            /** @var BenutzerIn $benutzerIn */
            $benutzerIn = BenutzerIn::model()->findByPk($x[0]);
            if (!$benutzerIn) $msg_err = "Diese Seite existiert nicht. Vielleicht wurde der Bestätigungslink falsch kopiert?";
            elseif ($benutzerIn->email_bestaetigt) $msg_err = "Dieser Zugang wurde bereits bestätigt.";
            elseif (!$benutzerIn->emailBestaetigen($code)) $msg_err = "Diese Seite existiert nicht. Vielleicht wurde der Bestätigungslink falsch kopiert? (Beachte, dass der Link in der E-Mail nur 2-3 Tage lang gültig ist.";
            else {
                $msg_ok   = "Der Zugang wurde bestätigt. Ab jetzt erhältst du Benachrichtigungen per E-Mail, wenn du das so eingestellt hast.";
                $identity = new RISUserIdentity($benutzerIn);
                Yii::app()->user->login($identity);
            }
        }


        if (AntiXSS::isTokenSet("abmelden") && !$user->isGuest) {
            $user->logout();
        }

        if (AntiXSS::isTokenSet("login_anlegen") && $user->isGuest && !isset($_REQUEST["register"])) {
            /** @var BenutzerIn $benutzerIn */
            $benutzerIn = BenutzerIn::model()->findByAttributes(array("email" => $_REQUEST["email"]));
            if ($benutzerIn) {
                if ($benutzerIn->validate_password($_REQUEST["password"])) {
                    $identity = new RISUserIdentity($benutzerIn);
                    Yii::app()->user->login($identity);
                } else {
                    $msg_err = "Das angegebene Passwort ist falsch.";
                }
            } else {
                $msg_err = "Für die angegebene E-Mail-Adresse existiert noch kein Zugang.";
            }
        }

        if (AntiXSS::isTokenSet("login_anlegen") && $user->isGuest && isset($_REQUEST["register"])) {
            /** @var BenutzerIn[] $gefundene_benutzerInnen */
            $gefundene_benutzerInnen = BenutzerIn::model()->findAll(array(
                "condition" => "email='" . addslashes($_REQUEST["email"]) . "'"
            ));
            if (count($gefundene_benutzerInnen) > 0) {
                $msg_err = "Es existiert bereits ein Zugang für diese E-Mail-Adresse";
            } elseif (trim($_REQUEST["password"]) == "") {
                $msg_err = "Bitte gib ein Passwort an";
            } elseif ($_REQUEST["password"] != $_REQUEST["password2"]) {
                $msg_err = "Die beiden angegebenen Passwörter stimmen nicht überein.";
            } else {

                $benutzerIn = BenutzerIn::createBenutzerIn(trim($_REQUEST["email"]), $_REQUEST["password"]);
                if ($benutzerIn->save()) {
                    $benutzerIn->sendEmailBestaetigungsMail();
                    $identity = new RISUserIdentity($benutzerIn);
                    Yii::app()->user->login($identity);

                    $msg_ok = "Der Zugang wurde angelegt. Es wurde eine Bestätigungs-Mail an die angegebene Adresse geschickt. Bitte klicke auf den Link in dieser Mail an, um E-Mail-Benachrichtigungen zu erhalten.";
                } else {
                    $msg_err = "Leider ist ein (ungewöhnlicher) Fehler aufgetreten.";
                    $errs    = $benutzerIn->getErrors();
                    foreach ($errs as $err) foreach ($err as $e) $msg_err .= $e;
                }
            }
        }

        return array($msg_ok, $msg_err);
    }

    /**
     * @param string $target_url
     * @param string $code
     * @return array
     */
    protected function requireLogin($target_url, $code = "")
    {
        list($msg_ok, $msg_err) = $this->performLoginActions($code);

        if (Yii::app()->getUser()->isGuest) {
            $this->render("../index/login", array(
                "current_url" => $target_url,
                "msg_err"     => $msg_err,
                "msg_ok"      => $msg_ok,
            ));
            Yii::app()->end();
        } else {
            $benutzerIn = $this->aktuelleBenutzerIn();
            if (!$benutzerIn) {
                Yii::app()->getUser()->logout();
                $this->redirect("/");
            }
        }

        return array($msg_ok, $msg_err);
    }

    /**
     * @return BenutzerIn|null
     */
    public function aktuelleBenutzerIn()
    {
        $user = Yii::app()->getUser();
        if ($user->isGuest) return null;
        /** @var BenutzerIn $ich */
        $ich = BenutzerIn::model()->findByAttributes(array("email" => Yii::app()->user->id));
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
        $this->render("../index/error", array(
            "code"    => $error_code,
            "message" => $error_message,
        ));
        Yii::app()->end($error_code);
        die();
    }
}
