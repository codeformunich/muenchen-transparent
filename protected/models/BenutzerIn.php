<?php

/**
 * @property integer $id
 * @property string $email
 * @property integer $email_bestaetigt
 * @property string $datum_angelegt
 * @property string $pwd_enc
 * @property string $einstellungen
 * @property string $datum_letzte_benachrichtigung
 *
 * @property AntragAbo[] $abonnierte_antraege
 */
class BenutzerIn extends CActiveRecord
{

	/** @var null|BenutzerInnenEinstellungen */
	private $einstellungen_object = null;


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
			array('email, datum_angelegt, pwd_enc', 'required'),
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
			'aenderungsantragKommentare'         => array(self::HAS_MANY, 'AenderungsantragKommentar', 'verfasserIn_id'),
			'aenderungsantragUnterstuetzerInnen' => array(self::HAS_MANY, 'AenderungsantragUnterstuetzer', 'unterstuetzerIn_id'),
			'antragKommentare'                   => array(self::HAS_MANY, 'AntragKommentar', 'verfasserIn_id'),
			'antragUnterstuetzerInnen'           => array(self::HAS_MANY, 'AntragUnterstuetzerInnen', 'unterstuetzerIn_id'),
			'admin_veranstaltungen'              => array(self::MANY_MANY, 'Veranstaltung', 'veranstaltungs_admins(person_id, veranstaltung_id)'),
			'admin_veranstaltungsreihen'         => array(self::MANY_MANY, 'Veranstaltungsreihe', 'veranstaltungsreihen_admins(person_id, veranstaltungsreihe_id)'),
			'veranstaltungsreihenAbos'           => array(self::HAS_MANY, 'VeranstaltungsreihenAbo', 'person_id'),
			'abonnierte_antraege'                => array(self::HAS_MANY, 'AntragAbo', 'antraege_abos(benutzerIn_id, antrag_id)'),
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
			'datum_angelegt'                => Yii::t('app', 'Angelegt Datum'),
			'datum_letzte_benachrichtigung' => Yii::t('app', 'Datum der letzten Benachrichtigung'),
			'einstellungen'                 => null,
			'abonnierte_antraege'           => null,

		);
	}

	/**
	 * @return CActiveDataProvider
	 */
	public function search()
	{
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('email', $this->email, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
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
	 * @return string
	 */
	public function getBenachrichtigungAbmeldenCode()
	{
		$code = $this->id . "-" . substr(md5($this->id . "abmelden" . SEED_KEY), 0, 8);
		return $code;
	}

	/**
	 * @param RISSucheKrits $krits
	 */
	public function addBenachrichtigung($krits) {
		$einstellungen = $this->getEinstellungen();
		$einstellungen->benachrichtigungen[] = $krits->krits;
		$this->setEinstellungen($einstellungen);
		$this->save();
	}

	/**
	 * @param RISSucheKrits $krits
	 */
	public function delBenachrichtigung($krits) {
		$suchkrits = $krits->getBenachrichtigungKrits();
		$einstellungen = $this->getEinstellungen();
		$neue = array();
		foreach ($einstellungen->benachrichtigungen as $ben) if ($suchkrits->krits != $ben) $neue[] = $ben;
		$einstellungen->benachrichtigungen = $neue;
		$this->save();
	}

	/**
	 * @return RISSucheKrits[]
	 */
	public function getBenachrichtigungen() {
		$arr = array();
		$einstellungen = $this->getEinstellungen();
		foreach ($einstellungen->benachrichtigungen as $krit) $arr[] = new RISSucheKrits($krit);
		return $arr;
	}

	/**
	 * @param RISSucheKrits $krits
	 * @return bool
	 */
	public function wirdBenachrichtigt($krits) {
		$suchkrits = $krits->getBenachrichtigungKrits();
		$einstellungen = $this->getEinstellungen();
		foreach ($einstellungen->benachrichtigungen as $ben) if ($suchkrits->krits == $ben) return true;
		return false;
	}


	/**
	 * @param string $a
	 * @param string $b
	 * @return bool
	 */
	private function slow_equals($a, $b)
	{
		$diff = strlen($a) ^ strlen($b);
		for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $diff === 0;
	}


	/**
	 * @static
	 * @param string $password
	 * @return string
	 */
	public static function create_hash($password)
	{
		// from: http://crackstation.net/hashing-security.htm
		// format: algorithm:iterations:salt:hash
		$salt = base64_encode(mcrypt_create_iv(24, MCRYPT_DEV_URANDOM));
		return "sha256:1000:" . $salt . ":" . base64_encode(static::pbkdf2("sha256", $password, $salt, 1000, 24, true));
	}


	/*
	 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
	 * $algorithm - The hash algorithm to use. Recommended: SHA256
	 * $password - The password.
	 * $salt - A salt that is unique to the password.
	 * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
	 * $key_length - The length of the derived key in bytes.
	 * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
	 * Returns: A $key_length-byte key derived from the password and salt.
	 *
	 * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
	 *
	 * This implementation of PBKDF2 was originally created by https://defuse.ca
	 * With improvements by http://www.variations-of-shadow.com
	 */
	/**
	 * @param string $algorithm
	 * @param string $password
	 * @param string $salt
	 * @param int $count
	 * @param int $key_length
	 * @param bool $raw_output
	 * @return string
	 */
	private static function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
	{
		$algorithm = strtolower($algorithm);
		if (!in_array($algorithm, hash_algos(), true))
			die('PBKDF2 ERROR: Invalid hash algorithm.');
		if ($count <= 0 || $key_length <= 0)
			die('PBKDF2 ERROR: Invalid parameters.');

		$hash_length = strlen(hash($algorithm, "", true));
		$block_count = ceil($key_length / $hash_length);

		$output = "";
		for ($i = 1; $i <= $block_count; $i++) {
			// $i encoded as 4 bytes, big endian.
			$last = $salt . pack("N", $i);
			// first iteration
			$last = $xorsum = hash_hmac($algorithm, $last, $password, true);
			// perform the other $count - 1 iterations
			for ($j = 1; $j < $count; $j++) {
				$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
			}
			$output .= $xorsum;
		}

		if ($raw_output)
			return substr($output, 0, $key_length);
		else
			return bin2hex(substr($output, 0, $key_length));
	}


	/**
	 * @param string $password
	 * @return bool
	 */
	public function validate_password($password)
	{
		$params = explode(":", $this->pwd_enc);
		if (count($params) < 4)
			return false;
		$pbkdf2 = base64_decode($params[3]);
		return $this->slow_equals(
			$pbkdf2,
			static::pbkdf2(
				$params[0],
				$password,
				$params[2],
				(int)$params[1],
				strlen($pbkdf2),
				true
			)
		);
	}

}