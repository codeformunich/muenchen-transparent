<?php

class IndexController extends RISBaseController
{

	public function actionFeed()
	{
		if (isset($_REQUEST["krit_typ"])) {
			$krits = RISSucheKrits::createFromUrl();
			$titel = "OpenRIS: " . $krits->getTitle();

			$solr   = RISSolrHelper::getSolrClient("ris");
			$select = $solr->createSelect();

			$krits->addKritsToSolr($select);

			$select->setRows(100);
			$select->addSort('sort_datum', $select::SORT_DESC);

			/** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
			$hl = $select->getHighlighting();
			$hl->setFields('text, text_ocr, antrag_betreff');
			$hl->setSimplePrefix('<b>');
			$hl->setSimplePostfix('</b>');

			$ergebnisse = $solr->select($select);

			$data = RISSolrHelper::ergebnisse2Feed($ergebnisse);
		} else {
			$data = array();
			/** @var array|RISAenderung[] $aenderungen */
			$aenderungen = RISAenderung::model()->findAll(array("order" => "id DESC", "limit" => 100));
			foreach ($aenderungen as $aenderung) $data[] = $aenderung->toFeedData();
			$titel = "OpenRIS Änderungen";
		}

		$this->render("feed", array(
			"feed_title"       => $titel,
			"feed_description" => $titel,
			"data"             => $data,
		));
	}

	public function actionAjaxEmailIstRegistriert($email)
	{
		$person = BenutzerIn::model()->findAll(array(
			"condition" => "email='" . addslashes($email) . "' AND pwd_enc != ''"
		));
		if (count($person) > 0) {
			/** @var BenutzerIn $p */
			$p = $person[0];
			if ($p->email_bestaetigt) echo "1";
			else echo "0";
		} else {
			echo "-1";
		}
	}

	/**
	 * @param RISSucheKrits $curr_krits
	 * @param string $code
	 * @return array
	 */
	protected function sucheBenachrichtigungenAnmelden($curr_krits, $code)
	{
		$user = Yii::app()->getUser();

		$correct_person      = null;
		$msg_ok              = $msg_err = "";
		$wird_benachrichtigt = false;

		if ($code != "") {
			$x = explode("-", $code);
			/** @var BenutzerIn $benutzerIn */
			$benutzerIn = BenutzerIn::model()->findByPk($x[0]);
			if (!$benutzerIn) $msg_err = "Diese Seite existiert nicht. Vielleicht wurde der Bestätigungslink falsch kopiert?";
			elseif ($benutzerIn->email_bestaetigt) $msg_err = "Dieser Account wurde bereits bestätigt."; elseif (!$benutzerIn->checkEmailBestaetigungsCode($code)) $msg_err = "Diese Seite existiert nicht. Vielleicht wurde der Bestätigungslink falsch kopiert? (Beachte, dass der Link in der E-Mail nur 2-3 Tage lang gültig ist."; else {
				$benutzerIn->email_bestaetigt = 1;
				$benutzerIn->save();
				$msg_ok   = "Der Zugang wurde bestätigt. Ab jetzt erhältst du Benachrichtigungen per E-Mail, wenn du das so eingestellt hast.";
				$identity = new RISUserIdentity($benutzerIn);
				Yii::app()->user->login($identity);
				$wird_benachrichtigt = true;
			}
		} elseif (AntiXSS::isTokenSet("anmelden")) {

			$benutzerIn = BenutzerIn::model()->findAll(array(
				"condition" => "email='" . addslashes($_REQUEST["email"]) . "' AND pwd_enc != ''"
			));
			if (count($benutzerIn) > 0) {
				/** @var BenutzerIn $p */
				$p = $benutzerIn[0];
				if ($p->email_bestaetigt) {
					if ($p->validate_password($_REQUEST["password"])) {
						$correct_person = $p;
						$correct_person->addBenachrichtigung($curr_krits);

						$identity = new RISUserIdentity($p);
						Yii::app()->user->login($identity);
						$wird_benachrichtigt = true;
					} else {
						$msg_err = "Das angegebene Passwort ist leider falsch.";
					}
				} else {
					if ($p->checkEmailBestaetigungsCode($_REQUEST["bestaetigungscode"])) {
						$p->email_bestaetigt = 1;
						if ($p->save()) {
							$p->addBenachrichtigung($curr_krits);
							$msg_ok   = "Die E-Mail-Adresse wurde freigeschaltet. Ab jetzt wirst du entsprechend deinen Einstellungen benachrichtigt.";
							$identity = new RISUserIdentity($p);
							Yii::app()->user->login($identity);
							$wird_benachrichtigt = true;
						} else {
							$msg_err = "Ein sehr seltsamer Fehler ist aufgetreten.";
						}
					} else {
						$msg_err = "Leider stimmt der angegebene Code nicht";
					}
				}
			} else {
				$email                        = trim($_REQUEST["email"]);
				$passwort                     = BenutzerIn::createPassword();
				$benutzerIn                   = new BenutzerIn;
				$benutzerIn->email            = $email;
				$benutzerIn->email_bestaetigt = 0;
				$benutzerIn->pwd_enc          = BenutzerIn::create_hash($passwort);
				$benutzerIn->datum_angelegt   = new CDbExpression("NOW()");

				if ($benutzerIn->save()) {
					$best_code = $benutzerIn->createEmailBestaetigungsCode();
					$link      = Yii::app()->getBaseUrl(true) . $this->createUrl("index/benachrichtigungen", array("code" => $best_code));
					mail($email, "Anmeldung bei OpenRIS", "Hallo,\n\num Benachrichtigungen bei OpenRIS zu erhalten, klicke entweder auf folgenden Link:\n$link\n\n"
						. "...oder gib, wenn du auf OpenRIS danach gefragt wirst, folgenden Code ein: $best_code\n\n"
						. "Das Passwort für den OpenRIS-Zugang lautet: " . $passwort . "\n\n"
						. "Liebe Grüße,\n\tDas OpenRIS-Team.");
					$correct_person = $benutzerIn;

					$correct_person->addBenachrichtigung($curr_krits);

					$identity = new RISUserIdentity($benutzerIn);
					Yii::app()->user->login($identity);
					$wird_benachrichtigt = true;
				} else {
					$msg_err = "Leider ist ein (ungewöhnlicher) Fehler aufgetreten.";
					$errs    = $benutzerIn->getErrors();
					foreach ($errs as $err) foreach ($err as $e) $msg_err .= $e;
				}
			}
		} else {
			if (!$user->isGuest) {
				/** @var BenutzerIn $ich */
				$ich = BenutzerIn::model()->findByAttributes(array("email" => Yii::app()->user->id));

				if (AntiXSS::isTokenSet("benachrichtigung_add")) {
					$ich->addBenachrichtigung($curr_krits);
				}
				if (AntiXSS::isTokenSet("benachrichtigung_del")) {
					$ich->delBenachrichtigung($curr_krits);
				}

				$wird_benachrichtigt = $ich->wirdBenachrichtigt($curr_krits);
			}
		}

		if ($user->isGuest) {
			$ich              = null;
			$eingeloggt       = false;
			$email_angegeben  = false;
			$email_bestaetigt = false;
		} else {
			$eingeloggt = true;
			/** @var BenutzerIn $ich */
			if (!$ich) $ich = BenutzerIn::model()->findByAttributes(array("email" => Yii::app()->user->id));
			if ($ich->email == "") {
				$email_angegeben  = false;
				$email_bestaetigt = false;
			} elseif ($ich->email_bestaetigt) {
				$email_angegeben  = true;
				$email_bestaetigt = true;
			} else {
				$email_angegeben  = true;
				$email_bestaetigt = false;
			}
		}

		return array(
			"eingeloggt"          => $eingeloggt,
			"email_angegeben"     => $email_angegeben,
			"email_bestaetigt"    => $email_bestaetigt,
			"wird_benachrichtigt" => $wird_benachrichtigt,
			"ich"                 => $ich,
			"msg_err"             => $msg_err,
			"msg_ok"              => $msg_ok,
		);
	}


	public function actionSuche($code = "")
	{
		if (isset($_POST["suchbegriff"])) {
			$suchbegriff = $_POST["suchbegriff"];
			$krits       = new RISSucheKrits();
			$krits->addVolltextsucheKrit($suchbegriff);
		} else {
			$krits       = RISSucheKrits::createFromUrl();
			$suchbegriff = $krits->getTitle();
		}


		$benachrichtigungen_optionen = $this->sucheBenachrichtigungenAnmelden($krits, $code);


		$solr   = RISSolrHelper::getSolrClient("ris");
		$select = $solr->createSelect();

		$krits->addKritsToSolr($select);


		$select->setRows(100);
		$select->addSort('sort_datum', $select::SORT_DESC);

		/** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
		$hl = $select->getHighlighting();
		$hl->setFields('text, text_ocr, antrag_betreff');
		$hl->setSimplePrefix('<b>');
		$hl->setSimplePostfix('</b>');

		$facetSet = $select->getFacetSet();
		$facetSet->createFacetField('antrag_typ')->setField('antrag_typ');
		$facetSet->createFacetField('antrag_wahlperiode')->setField('antrag_wahlperiode');

		$ergebnisse = $solr->select($select);


		$this->render("suchergebnisse", array_merge(array(
			"krits"       => $krits,
			"suchbegriff" => $suchbegriff,
			"ergebnisse"  => $ergebnisse,
		), $benachrichtigungen_optionen));
	}



	public function actionDokument($id)
	{
		/** @var AntragDokument $dokument */
		$dokument     = AntragDokument::model()->findByPk($id);
		$morelikethis = $dokument->solrMoreLikeThis();
		$this->render("dokument_intern", array(
			"dokument"     => $dokument,
			"morelikethis" => $morelikethis,
		));
	}

	public function actionStadtrat()
	{
		echo "Stadtrat";
	}

	public function actionBa($ba_nr)
	{
		$this->top_menu = "ba";

		$ba = Bezirksausschuss::model()->findByPk($ba_nr);
		$this->render("ba_uebersicht", array(
			"ba" => $ba
		));
	}

	/**
	 * @param Antrag[] $antraege
	 * @return array
	 */
	protected function antraege2geodata(&$antraege) {
		$geodata = array();
		foreach ($antraege as $ant) {
			foreach ($ant->dokumente as $dokument) {
				foreach ($dokument->orte as $ort) {
					$str = "<div class='antraglink'>" . CHtml::link($ant->getName(), $ant->getLink()) . "</div>";
					$str .= "<div class='ort_dokument'>";
					$str .= "<div class='ort'>" . CHtml::encode($ort->ort->ort) . "</div>";
					$str .= "<div class='dokument'>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</div>";
					$str .= "</div>";
					$geodata[] = array(
						FloatVal($ort->ort->lat),
						FloatVal($ort->ort->lon),
						$str
					);
				}
			}
		}
		return $geodata;
	}

	public function actionAntraegeAjax($datum_max) {
		$x = explode("-", $datum_max);
		$time = mktime(0, 0, 0, $x[1], $x[2], $x[0]);

		$i = 0;
		do {
			$datum = date("Y-m-d", $time - 3600*24*$i);
			/** @var array|Antrag[] $antraege */
			$antraege = Antrag::model()->neueste_stadtratsantragsdokumente($datum . " 00:00:00", $datum . " 23:59:59")->findAll();
			$i++;
		} while (count($antraege) == 0);

		$geodata = $this->antraege2geodata($antraege);

		ob_start();
		$this->renderPartial('index_antraege_liste', array(
			"antraege" => $antraege,
			"geodata" => $geodata,
			"datum" => $datum,
			"datum_pre" => date("Y-m-d", RISTools::date_iso2timestamp($datum . " 00:00:00") - 1),
		));

		Header("Content-Type: application/json; charset=UTF-8");
		echo json_encode(array(
			"datum" => $datum,
			"html" => ob_get_clean()
		));
		Yii::app()->end();
	}

	public function actionIndex()
	{
		$i = 0;
		do {
			$datum = date("Y-m-d", time() - 3600*24*$i);
			/** @var array|Antrag[] $antraege */
			$antraege = Antrag::model()->neueste_stadtratsantragsdokumente($datum . " 00:00:00", $datum . " 23:59:59")->findAll();
			$i++;
		} while (count($antraege) == 0);

		$geodata = $this->antraege2geodata($antraege);

		$this->render('index', array(
			"antraege" => $antraege,
			"geodata" => $geodata,
			"datum" => $datum,
			"datum_pre" => date("Y-m-d", RISTools::date_iso2timestamp($datum . " 00:00:00") - 1),
		));
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		} else {
			$this->render('error', array("code" => 400, "message" => "Ein Fehler ist aufgetreten"));
		}
	}

}