<?php

/**
 * @property integer $id
 * @property string $email
 * @property integer $email_bestaetigt
 * @property string $datum_angelegt
 * @property string $pwd_enc
 * @property string $pwd_change_date
 * @property string $pwd_change_code
 * @property string $einstellungen
 * @property string $datum_letzte_benachrichtigung
 *
 * @property Vorgang[] $abonnierte_vorgaenge
 */
class BenutzerIn extends CActiveRecord
{

	/** @var null|BenutzerInnenEinstellungen */
	private $einstellungen_object = null;

	/**
	 * @param string $email
	 * @param string $password
	 * @return BenutzerIn
	 */
	public static function createBenutzerIn($email, $password = "")
	{
		$benutzerIn                                = new BenutzerIn;
		$benutzerIn->email                         = $email;
		$benutzerIn->email_bestaetigt              = 0;
		$benutzerIn->pwd_enc                       = ($password != "" ? BenutzerIn::create_hash($password) : "");
		$benutzerIn->datum_angelegt                = new CDbExpression("NOW()");
		$benutzerIn->datum_letzte_benachrichtigung = new CDbExpression("NOW()");
		return $benutzerIn;
	}

	/**
	 * @return BenutzerInnenEinstellungen
	 */
	public function getEinstellungen()
	{
		if (!is_object($this->einstellungen_object)) $this->einstellungen_object = new BenutzerInnenEinstellungen($this->einstellungen);
		return $this->einstellungen_object;
	}

	/**
	 * @param BenutzerInnenEinstellungen $einstellungen
	 */
	public function setEinstellungen($einstellungen)
	{
		$this->einstellungen_object = $einstellungen;
		$this->einstellungen        = $einstellungen->toJSON();
	}

	/**
	 * @param string $className active record class name.
	 * @return BenutzerIn the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string
	 */
	public function tableName()
	{
		return 'benutzerInnen';
	}


	/**
	 * @return array
	 */
	public function rules()
	{
		$rules = array(
			array('email, datum_angelegt', 'required'),
			array('id, email_bestaetigt', 'numerical', 'integerOnly' => true),
			array('datum_letzte_benachrichtigung', 'default', 'setOnEmpty' => true, 'value' => null),
		);
		return $rules;
	}

	/**
	 * @return array
	 */
	public function relations()
	{
		return array(
			'abonnierte_vorgaenge' => array(self::MANY_MANY, 'Vorgang', 'benutzerInnen_vorgaenge_abos(benutzerInnen_id, vorgaenge_id)'),
		);
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
			'id'                            => Yii::t('app', 'ID'),
			'email'                         => Yii::t('app', 'E-Mail'),
			'email_bestaetigt'              => Yii::t('app', 'E-Mail-Adresse bestätigt'),
			'pwd_enc'                       => Yii::t('app', 'Passwort-Hash'),
			'pwd_change_date'               => Yii::t('app', 'Passwort-Änderung: Datum'),
			'pwd_change_code'               => Yii::t('app', 'Passwort-Änderung: Code'),
			'datum_angelegt'                => Yii::t('app', 'Angelegt Datum'),
			'datum_letzte_benachrichtigung' => Yii::t('app', 'Datum der letzten Benachrichtigung'),
			'einstellungen'                 => null,
			'abonnierte_vorgaenge'          => Yii::t('app', 'Abonnierte Vorgänge'),
		);
	}

	/**
	 * @param int $n
	 * @return string
	 */
	public static function label($n = 1)
	{
		return Yii::t('app', 'BenutzerIn|BenutzerInnen', $n);
	}


	/**
	 * @return string
	 */
	public static function createPassword()
	{
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$max   = strlen($chars) - 1;
		$pw    = "";
		for ($i = 0; $i < 8; $i++) $pw .= $chars[rand(0, $max)];
		return $pw;
	}

	/**
	 * @param string $date
	 * @return string
	 */
	public function createEmailBestaetigungsCode($date = "")
	{
		if ($date == "") $date = date("Ymd");
		$code = $this->id . "-" . substr(md5($this->id . $date . SEED_KEY), 0, 8);
		return $code;
	}

	/**
	 * @param string $code
	 * @return bool
	 */
	public function checkEmailBestaetigungsCode($code)
	{
		if ($code == $this->createEmailBestaetigungsCode()) return true;
		if ($code == $this->createEmailBestaetigungsCode(date("Ymd", time() - 24 * 3600))) return true;
		if ($code == $this->createEmailBestaetigungsCode(date("Ymd", time() - 2 * 24 * 3600))) return true;
		return false;
	}

	/**
	 *
	 */
	public function sendEmailBestaetigungsMail()
	{
		$best_code = $this->createEmailBestaetigungsCode();
		$link      = Yii::app()->getBaseUrl(true) . Yii::app()->createUrl("index/benachrichtigungen", array("code" => $best_code));
		RISTools::send_email($this->email, "Anmeldung beim Ratsinformant", "Hallo,\n\num deine E-Mail-Adresse zu bestätigen und E-Mail-Benachrichtigungen vom Ratsinformanten zu erhalten, klicke bitte auf folgenden Link:\n$link\n\n"
			. "Liebe Grüße,\n\tDas Ratsinformanten-Team.");
	}

	/**
	 * @param string $code
	 * @return bool
	 */
	public function emailBestaetigen($code)
	{
		if (!$this->checkEmailBestaetigungsCode($code)) return false;
		if ($this->pwd_enc == "") $this->pwd_enc = BenutzerIn::create_hash($code);
		$this->email_bestaetigt = 1;
		return $this->save();
	}

	/**
	 * @return string
	 */
	public function getBenachrichtigungAbmeldenCode()
	{
		$code = $this->id . "-" . substr(md5($this->id . "abmelden" . SEED_KEY), 0, 8);
		return $code;
	}


	/**
	 * @return bool|string
	 */
	public function resetPasswordStart()
	{
		if ($this->pwd_change_date !== null) {
			$ts = RISTools::date_iso2timestamp($this->pwd_change_date);
			if (time() - $ts < 3600 * 24) return "Es kann nur eine Passwortänderung innerhalb von 24 Stunden beantragt werden.";
		}
		$this->pwd_change_code = sha1(uniqid() . $this->pwd_enc);
		$this->pwd_change_date = new CDbExpression("NOW()");
		if ($this->save()) {
			$link = Yii::app()->getBaseUrl(true) . Yii::app()->createUrl("index/resetPassword", array("id" => $this->id, "code" => $this->pwd_change_code));
			RISTools::send_email($this->email, "Ratsinformant-Passwort zurücksetzen", "Hallo,\n\num ein neues Passwort für deinen Zugang beim Ratsinformanten zu setzen, klicke bitte auf folgenden Link:\n$link\n\n"
				. "Liebe Grüße,\n\tDas Ratsinformanten-Team.");
			return true;
		}
		return "Ein (ungewöhnlicher) Fehler ist aufgetreten.";
	}

	/**
	 * @param string $code
	 * @param string $new_pw
	 * @return string|bool
	 */
	public function resetPasswordDo($code, $new_pw)
	{
		if ($this->pwd_change_date === null) return "Es wurde keine Passwortänderung beantragt.";
		$ts = RISTools::date_iso2timestamp($this->pwd_change_date);
		if (time() - $ts > 3600 * 24) return "Der Antrag liegt bereits mehr als 24 Stunden zurück. Bitte stelle einen neuen Passwort-Änderungs-Antrag.";
		if ($this->pwd_change_code != $code) return "Ein ungültiger Link bzw. Code.";
		$this->pwd_enc         = BenutzerIn::create_hash($new_pw);
		$this->pwd_change_code = null;
		$this->save();
		return true;
	}

	/**
	 * @param string $new_pw
	 * @throws Exception
	 */
	public function setPassword($new_pw) {
		if (!defined("RATSINFORMANT_CALL_MODE") || RATSINFORMANT_CALL_MODE !== "shell") {
			throw new Exception("Diese Funktion kann nur über die Kommandozeile aufgerufen werden.");
		}
		$this->pwd_enc = BenutzerIn::create_hash($new_pw);
		$this->save(false);
	}

	/**
	 * @param RISSucheKrits $krits
	 */
	public function addBenachrichtigung($krits)
	{
		$einstellungen = $this->getEinstellungen();
		foreach ($einstellungen->benachrichtigungen as $ben) {
			if ($ben == $krits->krits) return;
		}
		$einstellungen->benachrichtigungen[] = $krits->krits;
		$this->setEinstellungen($einstellungen);
		$this->save();
	}

	/**
	 * @param RISSucheKrits $krits
	 */
	public function delBenachrichtigung($krits)
	{
		$suchkrits     = $krits->getBenachrichtigungKrits();
		$einstellungen = $this->getEinstellungen();
		$neue          = array();
		foreach ($einstellungen->benachrichtigungen as $ben) if ($suchkrits->krits != $ben) $neue[] = $ben;
		$einstellungen->benachrichtigungen = $neue;
		$this->setEinstellungen($einstellungen);
		$this->save();
	}

	/**
	 * @return RISSucheKrits[]
	 */
	public function getBenachrichtigungen()
	{
		$arr           = array();
		$einstellungen = $this->getEinstellungen();
		foreach ($einstellungen->benachrichtigungen as $krit) $arr[] = new RISSucheKrits($krit);
		return $arr;
	}

	/**
	 * @param RISSucheKrits $krits
	 * @return bool
	 */
	public function wirdBenachrichtigt($krits)
	{
		$suchkrits     = $krits->getBenachrichtigungKrits();
		$einstellungen = $this->getEinstellungen();
		foreach ($einstellungen->benachrichtigungen as $ben) if ($suchkrits->krits == $ben) return true;
		return false;
	}

	/**
	 * @param int[] $document_ids
	 * @param RISSucheKrits $benachrichtigung
	 * @return array
	 */
	public function queryBenachrichtigungen($document_ids, $benachrichtigung)
	{
		$solr = RISSolrHelper::getSolrClient("ris");

		$select = $solr->createSelect();

		$select->addSort('sort_datum', $select::SORT_DESC);
		$select->setRows(100);

		/** @var Solarium\QueryType\Select\Query\Component\DisMax $dismax */
		$dismax = $select->getDisMax();
		$dismax->setQueryParser('edismax');
		$dismax->setQueryFields("text text_ocr");

		$select->setQuery($benachrichtigung->getSolrQueryStr($select));

		$select->createFilterQuery('maxprice')->setQuery(implode(" OR ", $document_ids));

		/** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
		$hl = $select->getHighlighting();
		$hl->setFields('text, text_ocr, antrag_betreff');
		$hl->setSimplePrefix('<b>');
		$hl->setSimplePostfix('</b>');

		$ergebnisse = $solr->select($select);
		/** @var RISSolrDocument[] $documents */
		$documents = $ergebnisse->getDocuments();
		$res       = array();
		foreach ($documents as $document) {
			$res[] = array(
				"id"   => $document->id,
				"name" => $document->dokument_name . ", " . $document->antrag_betreff,
			);
		}
		return $res;
	}

	/**
	 * @param int $zeitspanne
	 * @return array
	 */
	public function benachrichtigungsErgebnisse($zeitspanne) {
		$benachrichtigungen = $this->getBenachrichtigungen();

		if ($zeitspanne > 0) {
			$neu_seit_ts = time() - $zeitspanne * 24 * 3600;
			$neu_seit    = date("Y-m-d H:i:s", $neu_seit_ts);
		} else {
			$neu_seit    = $this->datum_letzte_benachrichtigung;
			$neu_seit_ts = RISTools::date_iso2timestamp($neu_seit);
		}

		$ergebnisse = array(
			"antraege"  => array(),
			"termine"   => array(),
			"vorgaenge" => array(),
		);

		$sql = Yii::app()->db->createCommand();
		$sql->select("id")->from("dokumente")->where("datum >= '" . addslashes($neu_seit) . "'");
		$data = $sql->queryColumn(array("id"));
		if (count($data) > 0) {

			$document_ids = array();
			foreach ($data as $did) $document_ids[] = "id:\"Document:$did\"";

			foreach ($benachrichtigungen as $benachrichtigung) {
				$e = $this->queryBenachrichtigungen($document_ids, $benachrichtigung);
				foreach ($e as $f) {
					$d           = explode(":", $f["id"]);
					$dokument_id = IntVal($d[1]);
					$dokument    = Dokument::getCachedByID($dokument_id);
					if (!$dokument) continue;
					if ($dokument->antrag_id > 0) {
						if (!isset($ergebnisse["antraege"][$dokument->antrag_id])) $ergebnisse["antraege"][$dokument->antrag_id] = array(
							"antrag"    => $dokument->antrag,
							"dokumente" => array()
						);
						if (!isset($ergebnisse["antraege"][$dokument->antrag_id]["dokumente"][$dokument_id])) $ergebnisse["antraege"][$dokument->antrag_id]["dokumente"][$dokument_id] = array(
							"dokument" => Dokument::model()->findByPk($dokument_id),
							"queries"  => array()
						);
						$ergebnisse["antraege"][$dokument->antrag_id]["dokumente"][$dokument_id]["queries"][] = $benachrichtigung;
					} elseif ($dokument->termin_id > 0) {
						if (!isset($ergebnisse["termine"][$dokument->termin_id])) $ergebnisse["termine"][$dokument->termin_id] = array(
							"termin"    => $dokument->termin,
							"dokumente" => array()
						);
						if (!isset($ergebnisse["termine"][$dokument->termin_id]["dokumente"][$dokument_id])) $ergebnisse["termine"][$dokument->termin_id]["dokumente"][$dokument_id] = array(
							"dokument" => Dokument::model()->findByPk($dokument_id),
							"queries"  => array()
						);
						$ergebnisse["termine"][$dokument->termin_id]["dokumente"][$dokument_id]["queries"][] = $benachrichtigung;
					}
				}
			}
		}

		foreach ($this->abonnierte_vorgaenge as $vorgang) {
			foreach ($vorgang->antraege as $ant) {
				if (RISTools::date_iso2timestamp($ant->datum_letzte_aenderung) < $neu_seit_ts) continue;
				if (!isset($ergebnisse["vorgaenge"][$vorgang->id])) $ergebnisse["vorgaenge"][$vorgang->id] = array("vorgang" => $vorgang->wichtigstesRisItem()->getName(true), "neues" => array());
				$ant->findeAenderungen(time());
				$ergebnisse["vorgaenge"][$vorgang->id]["neues"][] = $ant;
			}
			foreach ($vorgang->dokumente as $dok) {
				if (RISTools::date_iso2timestamp($dok->datum) < $neu_seit_ts) continue;
				if (!isset($ergebnisse["vorgaenge"][$vorgang->id])) $ergebnisse["vorgaenge"][$vorgang->id] = array("vorgang" => $vorgang->wichtigstesRisItem()->getName(true), "neues" => array());
				$ergebnisse["vorgaenge"][$vorgang->id]["neues"][] = $dok;
			}
			foreach ($vorgang->ergebnisse as $erg) {
				if (RISTools::date_iso2timestamp($erg->datum_letzte_aenderung) < $neu_seit_ts) continue;
				if (!isset($ergebnisse["vorgaenge"][$vorgang->id])) $ergebnisse["vorgaenge"][$vorgang->id] = array("vorgang" => $vorgang->wichtigstesRisItem()->getName(true), "neues" => array());
				$ergebnisse["vorgaenge"][$vorgang->id]["neues"][] = $erg;
			}
		}

		return $ergebnisse;
	}


	/**
	 * @param string $code
	 * @return BenutzerIn|null
	 */
	public static function getByFeedCode($code)
	{
		$x = explode("-", $code);
		if (count($x) != 2) return null;
		/** @var BenutzerIn $benutzerIn */
		$benutzerIn = BenutzerIn::model()->findByPk($x[0]);
		if (!$benutzerIn) return null;
		elseif ($code == $benutzerIn->getFeedCode()) return $benutzerIn;
		else return null;
	}

	/**
	 * @return string
	 */
	public function getFeedCode()
	{
		return $this->id . "-" . substr(md5(SEED_KEY . $this->pwd_enc), 0, 10);
	}


	/**
	 * @static
	 * @param string $password
	 * @return string
	 */
	public static function create_hash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}


	/**
	 * @param string $password
	 * @return bool
	 */
	public function validate_password($password)
	{
		return password_verify($password, $this->pwd_enc);
	}

}
