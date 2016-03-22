<?php

class BenachrichtigungenController extends RISBaseController
{
    public function actionIndex($code = "")
    {
        $this->top_menu = "benachrichtigungen";

        $this->requireLogin($this->createUrl("index/benachrichtigungen"), $code);

        /** @var BenutzerIn $ich */
        $ich = $this->aktuelleBenutzerIn();

        $this->load_leaflet = true;

        if (AntiXSS::isTokenSet("einstellungen_speichern")) {
            $einstellungen = $ich->getEinstellungen();
            if (isset($_REQUEST["intervall"]) && $_REQUEST["intervall"] == "tag") $einstellungen->benachrichtigungstag = null;
            if (isset($_REQUEST["intervall"]) && $_REQUEST["intervall"] == "woche") {
                if (isset($_REQUEST["wochentag"])) $einstellungen->benachrichtigungstag = IntVal($_REQUEST["wochentag"]);
            }
            $ich->setEinstellungen($einstellungen);
            $ich->save();
            $this->msg_ok = "Die Einstellung wurde gespeichert.";
        }

        if (AntiXSS::isTokenSet("del_ben")) {
            foreach ($_REQUEST[AntiXSS::createToken("del_ben")] as $ben => $_val) {
                $bena = json_decode(rawurldecode($ben), true);
                $krit = new RISSucheKrits($bena);
                $ich->delBenachrichtigung($krit);
                $this->msg_ok = "Die Benachrichtigung wurde entfernt.";
            }
        }

        if (AntiXSS::isTokenSet("benachrichtigung_add_text")) {
            $suchbegriff = trim($_REQUEST["suchbegriff"]);
            if ($suchbegriff == "") {
                $this->msg_err = "Bitte gib einen Suchausdruck an.";
            } else {
                $ben = new RISSucheKrits();
                $ben->addVolltextsucheKrit($suchbegriff);
                $ich->addBenachrichtigung($ben);
                $this->msg_ok = "Die Benachrichtigung wurde hinzugefügt.";
            }
        }

        if (AntiXSS::isTokenSet("benachrichtigung_add_ba")) {
            $ben = new RISSucheKrits();
            $ben->addBAKrit($_REQUEST["ba"]);
            $ich->addBenachrichtigung($ben);
            $this->msg_ok = "Die Benachrichtigung wurde hinzugefügt.";
        }

        if (AntiXSS::isTokenSet("benachrichtigung_add_geo")) {
            if ($_REQUEST["geo_lng"] == 0 || $_REQUEST["geo_lat"] == 0 || $_REQUEST["geo_radius"] <= 0) {
                $this->msg_err = "Ungültige Eingabe.";
            } else {
                $ben = new RISSucheKrits();
                $ben->addGeoKrit($_REQUEST["geo_lng"], $_REQUEST["geo_lat"], $_REQUEST["geo_radius"]);
                $ich->addBenachrichtigung($ben);
                $this->msg_ok = "Die Benachrichtigung wurde hinzugefügt.";
            }
        }

        if (AntiXSS::isTokenSet("del_vorgang_abo")) {
            foreach (AntiXSS::getTokenVal("del_vorgang_abo") as $vorgang_id => $_tmp) {
                /** @var Vorgang $vorgang */
                $vorgang = Vorgang::model()->findByPk($vorgang_id);
                $vorgang->deabonnieren($ich);
                $this->msg_ok = "Der Vorgang wurde entfernt.";
            }
        }

        if (AntiXSS::isTokenSet("account_loeschen")) {
            $this->top_menu = "Accountlöschung";
            $this->requireLogin($this->createUrl("index/benachrichtigungen"));
            $ich = $this->aktuelleBenutzerIn();
            $id  = $ich->id;

            if ($ich != NULL) {
                $ich->email                         = NULL;
                $ich->email_bestaetigt              = 0;
                $ich->pwd_enc                       = NULL;
                $ich->datum_angelegt                = NULL;
                $ich->datum_letzte_benachrichtigung = NULL;
                $ich->berechtigungen_flags          = 0;
                $ich->einstellungen_object          = NULL;
                $ich->einstellungen                 = NULL;
                $ich->save(false);

                Yii::app()->db
                    ->createCommand("DELETE FROM `benutzerInnen_vorgaenge_abos` WHERE `benutzerInnen_id` = :BenutzerInId")
                    ->bindValues([':BenutzerInId' => $ich->id])
                    ->execute();

                $this->msg_ok = "Account gelöscht";

                /** @var CWebUser $user */
                $user = Yii::app()->getUser();
                if ($user) $user->logout();
            } else {
                $this->msg_err = "Sie sind nicht angemeldet";
            }

            $this->redirect(Yii::app()->createUrl("index/startseite"));

            Yii::app()->end();
        }

        if (AntiXSS::isTokenSet("passwort_aendern")) {
            /** @var null|BenutzerIn $ich  */
            $ich = $this->aktuelleBenutzerIn();
            if ($ich) {
              if ($_REQUEST["password"] == $_REQUEST["password2"]) {
                    $ich->pwd_enc = BenutzerIn::create_hash($_REQUEST["password"]);
                    $ich->save();
                    $this->msg_ok = "Passwort geändert";
                } else {
                    $this->msg_err = "Die beiden Passwörter stimmen nicht überein";
                }
            } else {
                $this->render('/index/error', ["code" => 403, "message" => "Sie sind nicht angemeldet."]);
            }
        }

        $this->render("index", [
            "ich" => $ich,
        ]);
    }

    /**
     * @param Solarium\Client $solr
     * @param BenutzerIn $benutzerIn
     * @return \Solarium\QueryType\Select\Query\Query
     */
    protected function getAlleSuchergebnisse(&$solr, $benutzerIn)
    {
        $select = $solr->createSelect();

        $select->addSort('sort_datum', $select::SORT_DESC);
        $select->setRows(100);

        /** @var Solarium\QueryType\Select\Query\Component\DisMax $dismax */
        $dismax = $select->getDisMax();
        $dismax->setQueryParser('edismax');
        $dismax->setQueryFields("text text_ocr");

        $benachrichtigungen = $benutzerIn->getBenachrichtigungen();
        $krits_solr         = [];

        foreach ($benachrichtigungen as $ben) $krits_solr[] = "(" . $ben->getSolrQueryStr($select) . ")";
        $querystr = implode(" OR ", $krits_solr);

        $select->setQuery($querystr);

        /** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
        $hl = $select->getHighlighting();
        $hl->setFields('text, text_ocr, antrag_betreff');
        $hl->setSimplePrefix('<b>');
        $hl->setSimplePostfix('</b>');

        return $select;
    }

    /**
     * @param string $code
     */
    public function actionAlleFeed($code)
    {
        $benutzerIn = BenutzerIn::getByFeedCode($code);
        if (!$benutzerIn) {
            $this->render('../index/error', ["code" => 400, "message" => "Das Feed konnte leider nicht gefunden werden."]);
            return;
        }

        $titel       = "Suchergebnisse";
        $description = "Neue Dokumente, die einem der folgenden Kriterien entsprechen:<br>";
        $bens        = $benutzerIn->getBenachrichtigungen();
        foreach ($bens as $ben) $description .= "- " . CHtml::encode($ben->getTitle()) . "<br>";

        $solr       = RISSolrHelper::getSolrClient();
        $select     = $this->getAlleSuchergebnisse($solr, $benutzerIn);
        $ergebnisse = $solr->select($select);
        $data       = RISSolrHelper::ergebnisse2FeedData($ergebnisse);

        $this->render("../index/feed", [
            "feed_title"       => $titel,
            "feed_description" => $description,
            "data"             => $data,
        ]);

    }


    public function actionAlleSuchergebnisse()
    {
        $this->requireLogin($this->createUrl("index/benachrichtigungen"));

        /** @var BenutzerIn $ich */
        $ich = $this->aktuelleBenutzerIn();

        $solr   = RISSolrHelper::getSolrClient();
        $select = $this->getAlleSuchergebnisse($solr, $ich);

        $facetSet = $select->getFacetSet();
        $facetSet->createFacetField('antrag_typ')->setField('antrag_typ');
        $facetSet->createFacetField('antrag_wahlperiode')->setField('antrag_wahlperiode');

        $ergebnisse = $solr->select($select);


        $this->render("alle_suchergebnisse", [
            "ergebnisse" => $ergebnisse,
        ]);
    }


    public function actionNewsletterHTMLTest()
    {
        $benutzerIn = $this->aktuelleBenutzerIn();
        $data       = $benutzerIn->benachrichtigungsErgebnisse(31);

        $path = Yii::getPathOfAlias('application.views.benachrichtigungen') . '/suchergebnisse_email_html.php';
        if (!file_exists($path)) throw new Exception('Template ' . $path . ' does not exist.');
        require($path);
        Yii::app()->end();
    }

    public function actionPasswortZuruecksetzen()
    {
        if (AntiXSS::isTokenSet("reset_password")) {
            /** @var null|BenutzerIn $benutzerIn */
            $benutzerIn = BenutzerIn::model()->findByAttributes(["email" => $_REQUEST["email"]]);
            if ($benutzerIn) {
                $ret = $benutzerIn->resetPasswordStart();
                if ($ret === true) {
                    $this->render('reset_password_sent');
                } else {
                    $this->msg_err = $ret;
                    $this->render('reset_password_form');
                }
            } else {
                $this->msg_err = "Es gibt keinen Zugang mit dieser E-Mail-Adresse";
                $this->render('reset_password_form');
            }
        } else {
            $this->render('reset_password_form');
        }
    }

    /**
    * @param string $id
    * @param string $code
    */
    public function actionNeuesPasswortSetzen($id = "", $code = "")
    {
        $my_url = $this->createUrl("benachrichtigungen/NeuesPasswortSetzen", ["id" => $id, "code" => $code]);
        if (AntiXSS::isTokenSet("reset_password")) {
            /** @var null|BenutzerIn $benutzerIn */
            $benutzerIn = BenutzerIn::model()->findByPk($id);

            if (!$benutzerIn) {
                $this->msg_err = "BenutzerIn nicht gefunden";
                $this->render('reset_password_form', [
                    "current_url" => $this->createUrl("benachrichtigungen/PasswortZuruecksetzen"),
                ]);
                return;
            }

            if ($_REQUEST["password"] != $_REQUEST["password2"]) {
                $this->msg_err = "Die beiden Passwörter stimmen nicht überein";
                $this->render('reset_password_set_form', [
                    "current_url" => $my_url,
                ]);
                return;
            }

            $ret = $benutzerIn->resetPasswordDo($code, $_REQUEST["password"]);
            if ($ret === true) {
                $this->render('reset_password_done');
            } else {
                $this->msg_err = $ret;
                $this->render('reset_password_set_form', [
                    "current_url" => $my_url,
                ]);
            }
        } else {
            $this->render('reset_password_set_form', [
                "current_url" => $my_url,
            ]);
        }
    }



}
