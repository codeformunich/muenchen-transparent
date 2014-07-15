<?php

class RISBaseController extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout = '//layouts/column1';
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

	public $load_leaflet_css = false;
	public $load_leaflet_draw_css = false;
	public $suche_pre = "";

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

					if ($benutzerIn->email == Yii::app()->params['adminEmail']) Yii::app()->user->setState("role", "admin");
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
}