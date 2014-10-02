<?php

/**
 * This is the model class for table "antraege_ergebnisse".
 *
 * The followings are the available columns in table 'antraege_ergebnisse':
 * @property integer $id
 * @property integer $vorgang_id
 * @property string $datum_letzte_aenderung
 * @property integer $antrag_id
 * @property string $gremium_name
 * @property integer $gremium_id
 * @property integer $sitzungstermin_id
 * @property string $sitzungstermin_datum
 * @property string $beschluss_text
 * @property string $entscheidung
 * @property string $top_nr
 * @property int $top_ueberschrift
 * @property string $top_betreff
 * @property string $status
 *
 * The followings are the available model relations:
 * @property Termin $sitzungstermin
 * @property Gremium $gremium
 * @property Antrag $antrag
 * @property AntragDokument[] $dokumente
 */
class AntragErgebnis extends CActiveRecord implements IRISItem
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AntragErgebnis the static model class
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
		return 'antraege_ergebnisse';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('top_betreff, sitzungstermin_id, sitzungstermin_datum, datum_letzte_aenderung', 'required'),
			array('antrag_id, gremium_id, sitzungstermin_id, top_ueberschrift, vorgang_id', 'numerical', 'integerOnly' => true),
			array('gremium_name', 'length', 'max' => 100),
			array('beschluss_text', 'length', 'max' => 500),
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
			'sitzungstermin' => array(self::BELONGS_TO, 'Termin', 'sitzungstermin_id'),
			'gremium'        => array(self::BELONGS_TO, 'Gremium', 'gremium_id'),
			'antrag'         => array(self::BELONGS_TO, 'Antrag', 'antrag_id'),
			'dokumente'      => array(self::HAS_MANY, 'AntragDokument', 'ergebnis_id'),
			'vorgang'        => array(self::BELONGS_TO, 'Vorgang', 'vorgang_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'vorgang_id'             => 'Vorgangs-ID',
			'antrag_id'              => 'Antrag',
			'gremium_name'           => 'Gremium Name',
			'gremium_id'             => 'Gremium',
			'sitzungstermin_id'      => 'Sitzungstermin',
			'sitzungstermin_datum'   => 'Sitzungstermin Datum',
			'beschluss_text'         => 'Beschluss',
			'entscheidung'           => 'Entscheidung',
			'datum_letzte_aenderung' => 'Letzte Änderung',
			'top_nr'                 => 'Tagesordnungspunkt',
			'top_ueberschrift'       => 'Ist Überschrift',
			'top_betreff'            => 'Betreff',
			'status'                 => 'Status'
		);
	}

	/**
	 * @throws CDbException|Exception
	 */
	public function copyToHistory()
	{
		$history = new AntragErgebnisHistory();
		$history->setAttributes($this->getAttributes(), false);
		try {
			if (!$history->save()) {
				RISTools::send_email(Yii::app()->params['adminEmail'], "AntragErgebnisHistory:moveToHistory Error", print_r($history->getErrors(), true));
				throw new Exception("Fehler");
			}
		} catch (CDbException $e) {
			if (strpos($e->getMessage(), "Duplicate entry") === false) throw $e;
		}

	}

	/**
	 * @return OrtGeo[]
	 */
	public function get_geo()
	{
		$return            = array();
		$strassen_gefunden = RISGeo::suche_strassen($this->top_betreff);
		$indexed           = array();
		foreach ($strassen_gefunden as $strasse_name) if (!in_array($strasse_name, $indexed)) {
			$indexed[] = $strasse_name;
			$geo       = OrtGeo::getOrCreate($strasse_name);
			if (is_null($geo)) continue;
			$return[] = $geo;
		}
		return $return;
	}

	/**
	 * @return array
	 */
	public function zugeordneteAntraegeHeuristisch()
	{
		$betreff  = str_replace(array("\n", "\r"), array(" ", " "), $this->top_betreff);
		$x        = explode(" Antrag Nr.", $betreff);
		$antraege = array();
		foreach ($x as $y) if (preg_match("/[0-9]{2}\-[0-9]{2} \/ [A-Z] [0-9]+/su", $y, $match)) {
			/** @var Antrag $antrag */
			$antrag = Antrag::model()->findByAttributes(array("antrags_nr" => $match[0]));
			if ($antrag) $antraege[] = $antrag;
			else $antraege[] = "Antrag Nr." . $y;
		}
		return $antraege;
	}


	/**
	 * @return string
	 */
	public function getLink()
	{
		return $this->antrag->getLink();
	}


	/** @return string */
	public function getTypName()
	{
		return "Stadtratsbeschluss";
	}

	/**
	 * @param bool $kurzfassung
	 * @return string
	 */
	public function getName($kurzfassung = false)
	{
		if ($kurzfassung) {
			$betreff = str_replace(array("\n", "\r"), array(" ", " "), $this->top_betreff);
			$x       = explode(" Antrag Nr.", $betreff);
			$x       = explode("<strong>Antrag: </strong>", $x[0]);
			return RISTools::korrigiereTitelZeichen($x[0]);
		} else {
			return RISTools::korrigiereTitelZeichen($this->top_betreff);
		}
	}

	/**
	 * @return string
	 */
	public function getDate() {
		return $this->datum_letzte_aenderung;
	}


}