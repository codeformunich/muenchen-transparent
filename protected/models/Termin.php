<?php

/**
 * This is the model class for table "termine".
 *
 * The followings are the available columns in table 'termine':
 * @property integer $id
 * @property string $datum_letzte_aenderung
 * @property integer $termin_reihe
 * @property integer $gremium_id
 * @property integer $ba_nr
 * @property string $termin
 * @property integer $termin_prev_id
 * @property integer $termin_next_id
 * @property string $sitzungsort
 * @property string $referat
 * @property string $referent
 * @property string $vorsitz
 * @property string $wahlperiode
 * @property string $status
 *
 * The followings are the available model relations:
 * @property AntragDokument[] $antraegeDokumente
 * @property AntragErgebnis[] $antraegeErgebnisse
 * @property AntragOrt[] $antraegeOrte
 * @property Gremium $gremium
 */
class Termin extends CActiveRecord implements IRISItem
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Termin the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'termine';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, datum_letzte_aenderung, wahlperiode, status', 'required'),
			array('id, termin_reihe, gremium_id, ba_nr, termin_prev_id, termin_next_id', 'numerical', 'integerOnly' => true),
			array('referat, referent, vorsitz', 'length', 'max' => 200),
			array('wahlperiode', 'length', 'max' => 20),
			array('status', 'length', 'max' => 100),
			array('termin_reihe, gremium_id, ba_nr, termin, termin_prev_id, termin_next_id, sitzungsort, referat, referent, vorsitz, wahlperiode, status', 'safe'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'antraegeDokumente'  => array(self::HAS_MANY, 'AntragDokument', 'termin_id'),
			'antraegeErgebnisse' => array(self::HAS_MANY, 'AntragErgebnis', 'sitzungstermin_id'),
			'antraegeOrte'       => array(self::HAS_MANY, 'AntragOrt', 'termin_id'),
			'gremium'            => array(self::BELONGS_TO, 'Gremium', 'gremium_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'datum_letzte_aenderung' => 'Datum Letzte Aenderung',
			'termin_reihe'           => 'Termin Reihe',
			'gremium_id'             => 'Gremium',
			'ba_nr'                  => 'Ba Nr',
			'termin'                 => 'Termin',
			'termin_prev_id'         => 'Termin Prev',
			'termin_next_id'         => 'Termin Next',
			'sitzungsort'            => 'Sitzungsort',
			'referat'                => 'Referat',
			'referent'               => 'Referent',
			'vorsitz'                => 'Vorsitz',
			'wahlperiode'            => 'Wahlperiode',
			'status'                 => 'Status',
		);
	}

	/**
	 * @throws CDbException|Exception
	 */
	public function copyToHistory()
	{
		$history = new TerminHistory();
		$history->setAttributes($this->getAttributes(), false);
		if ($history->wahlperiode == "") $history->wahlperiode = "?";
		if ($history->status == "") $history->status = "?";
		if ($history->sitzungsort == "") $history->sitzungsort = "?";
		try {
			if (!$history->save()) {
				RISTools::send_email(Yii::app()->params['adminEmail'], "Termin:moveToHistory Error", print_r($history->getErrors(), true));
				throw new Exception("Fehler");
			}
		} catch (CDbException $e) {
			if (strpos($e->getMessage(), "Duplicate entry") === false) throw $e;
		}

	}

	/**
	 * @return string
	 */
	public function getLink()
	{
		return Yii::app()->createUrl("termine/anzeigen", array("id" => $this->id));
	}


	/** @return string */
	public function getTypName()
	{
		if ($this->ba_nr > 0) return "BA-Termin";
		else return "Stadtratstermin";
	}

	/**
	 * @param bool $kurzfassung
	 * @return string
	 */
	public function getName($kurzfassung = false)
	{
		return $this->gremium->name . " (" . $this->termin . ")";
	}

	/**
	 * @return string
	 */
	public function getDate()
	{
		return $this->datum_letzte_aenderung;
	}


	/**
	 * @return string
	 */
	public function getSourceLink()
	{
		if ($this->ba_nr > 0) return "http://www.ris-muenchen.de/RII/BA-RII/ba_sitzungen_details.jsp?Id=" . $this->id;
		else return "http://www.ris-muenchen.de/RII/RII/ris_sitzung_detail.jsp?risid=" . $this->id;
	}


	/**
	 * @param null|int $ba_nr
	 * @param string $zeit_von
	 * @param string $zeit_bis
	 * @param bool $aufsteigend
	 * @param int $limit
	 * @return $this
	 */
	public function termine_stadtrat_zeitraum($ba_nr, $zeit_von, $zeit_bis, $aufsteigend = true, $limit = 0)
	{
		$ba_sql = ($ba_nr > 0 ? " = " . IntVal($ba_nr) : " IS NULL ");
		$params = array(
			'condition' => 'termin.ba_nr ' . $ba_sql . ' AND termin >= "' . addslashes($zeit_von) . '" AND termin <= "' . addslashes($zeit_bis) . '"',
			'order'     => 'termin ' . ($aufsteigend ? "ASC" : "DESC"),
			'with'      => array("gremium"),
			'alias'     => 'termin'
		);
		if ($limit > 0) $params['limit'] = $limit;
		$this->getDbCriteria()->mergeWith($params);
		return $this;
	}


	public function getDokumente() {
		return $this->antraegeDokumente;
	}

	/**
	 * @param int $ba_nr
	 * @param string $zeit_von
	 * @param string $zeit_bis
	 * @param int $limit
	 * @return $this
	 */
	public function neueste_str_protokolle($ba_nr, $zeit_von, $zeit_bis, $limit = 0)
	{
		$ba_sql = "ba_nr IS NULL";

		$params = array(
			'condition' => $ba_sql . ' AND datum_letzte_aenderung >= "' . addslashes($zeit_von) . '" AND datum_letzte_aenderung <= "' . addslashes($zeit_bis) . '"',
			'order'     => 'datum DESC',
			'with'      => array(
				'antraegeDokumente' => array(
					'condition' => 'name like "%protokoll%" AND datum >= "' . addslashes($zeit_von) . '" AND datum <= "' . addslashes($zeit_bis) . '"',
				),
			));
		if ($limit > 0) $params['limit'] = $limit;
		$this->getDbCriteria()->mergeWith($params);
		return $this;
	}

	/**
	 * @param int $ba_nr
	 * @param string $zeit_von
	 * @param string $zeit_bis
	 * @param int $limit
	 * @return $this
	 */
	public function neueste_ba_dokumente($ba_nr, $zeit_von, $zeit_bis, $limit = 0)
	{
		$ba_sql = "ba_nr = " . IntVal($ba_nr);

		$params = array(
			'condition' => $ba_sql . ' AND datum_letzte_aenderung >= "' . addslashes($zeit_von) . '" AND datum_letzte_aenderung <= "' . addslashes($zeit_bis) . '"',
			'order'     => 'datum DESC',
			'with'      => array(
				'antraegeDokumente' => array(
					'condition' => 'datum >= "' . addslashes($zeit_von) . '" AND datum <= "' . addslashes($zeit_bis) . '"',
				),
			));
		if ($limit > 0) $params['limit'] = $limit;
		$this->getDbCriteria()->mergeWith($params);
		return $this;
	}

	/**
	 * @return AntragErgebnis[]
	 */
	public function ergebnisseSortiert()
	{
		$ergebnisse = $this->antraegeErgebnisse;
		usort($ergebnisse, function ($ergebnis1, $ergebnis2) {
			/** @var AntragErgebnis $ergebnis1 */
			/** @var AntragErgebnis $ergebnis2 */

			if ($ergebnis1->status == "geheim" && $ergebnis2->status != "geheim") return 1;
			if ($ergebnis1->status != "geheim" && $ergebnis2->status == "geheim") return -1;

			$nr1 = explode(".", $ergebnis1->top_nr);
			$nr2 = explode(".", $ergebnis2->top_nr);
			if ($nr1[0] > $nr2[0]) return 1;
			if ($nr1[0] < $nr2[0]) return -1;
			if (count($nr1) == 1 && count($nr2) == 1) return 0;
			if (count($nr1) >= 2 && count($nr2) == 1) return 1;
			if (count($nr1) == 1 && count($nr2) >= 2) return -1;
			if ($nr1[1] > $nr2[1]) return 1;
			if ($nr1[1] < $nr2[1]) return -1;
			if (count($nr1) == 2 && count($nr2) == 2) return 0;
			if (count($nr1) >= 3 && count($nr2) == 2) return 1;
			if (count($nr1) == 2 && count($nr2) >= 3) return -1;
			if ($nr1[2] > $nr2[2]) return 1;
			if ($nr1[2] < $nr2[2]) return -1;
			return 0;
		});
		return $ergebnisse;
	}


	/**
	 * @var Termin[] $appointments
	 * @return array[]
	 */
	public static function groupAppointments($appointments)
	{
		$data = array();
		foreach ($appointments as $appointment) {
			$key = $appointment->termin . $appointment->sitzungsort;
			if (!isset($data[$key])) {
				$ts         = RISTools::date_iso2timestamp($appointment->termin);
				$data[$key] = array(
					"id"         => $appointment->id,
					"link"       => $appointment->getLink(),
					"datum"      => strftime("%e. %b., %H:%M", $ts),
					"datum_long" => strftime("%e. %B, %H:%M Uhr", $ts),
					"datum_iso"  => $appointment->termin,
					"datum_ts"   => $ts,
					"gremien"    => array(),
					"ort"        => $appointment->sitzungsort,
					"tos"        => array(),
					"dokumente"  => $appointment->antraegeDokumente,
				);
			}
			$url = Yii::app()->createUrl("termine/anzeigen", array("termin_id" => $appointment->id));
			if (!isset($data[$key]["gremien"][$appointment->gremium->name])) $data[$key]["gremien"][$appointment->gremium->name] = array();
			$data[$key]["gremien"][$appointment->gremium->name][] = $url;
		}
		foreach ($data as $key => $val) ksort($data[$key]["gremien"]);
		return $data;
	}
}
