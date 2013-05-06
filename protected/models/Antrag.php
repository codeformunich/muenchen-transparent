<?php

/**
 * This is the model class for table "antraege".
 *
 * The followings are the available columns in table 'antraege':
 * @property integer $id
 * @property string $typ
 * @property string $datum_letzte_aenderung
 * @property integer $ba_nr
 * @property string $gestellt_am
 * @property string $gestellt_von
 * @property string $antrags_nr
 * @property string $bearbeitungsfrist
 * @property string $registriert_am
 * @property string $referat
 * @property string $referent
 * @property string $wahlperiode
 * @property string $antrag_typ
 * @property string $betreff
 * @property string $kurzinfo
 * @property string $status
 * @property string $bearbeitung
 * @property string $fristverlaengerung
 * @property string $initiatorInnen
 * @property string $initiative_to_aufgenommen
 *
 * The followings are the available model relations:
 * @property Bezirksausschuss $ba
 * @property AntragDokument[] $dokumente
 * @property AntragErgebnis[] $ergebnisse
 * @property AntragOrt[] $orte
 * @property AntragPerson[] $antraegePersonen
 * @property StadtraetIn[] $stadtraetInnen
 * @property Antrag[] $antrag2vorlagen
 * @property Antrag[] $vorlage2antraege
 * @property AntragAbo[] $abos
 */
class Antrag extends CActiveRecord implements IRISItem
{

	public static $TYP_STADTRAT_ANTRAG = "stadtrat_antrag";
	public static $TYP_STADTRAT_VORLAGE = "stadtrat_vorlage";
	public static $TYP_STADTRAT_VORLAGE_GEHEIM = "stadtrat_vorlage_geheim";
	public static $TYP_BA_ANTRAG = "ba_antrag";
	public static $TYP_BA_INITIATIVE = "ba_initiative";

	public static $TYPEN_ALLE = array(
		"stadtrat_antrag"         => "Stadtratsantrag|Stadtratsanträge",
		"stadtrat_vorlage"        => "Stadtratsvorlage|Stadtratsvorlagen",
		"ba_antrag"               => "BA-Antrag|BA-Anträge",
		"ba_initiative"           => "BA-Initiative|BA-Initiativen",
		"stadtrat_vorlage_geheim" => "Geheime Stadtratsvorlage|Geheime Stadtratsvorlagen",
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Antrag the static model class
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
		return 'antraege';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, typ, datum_letzte_aenderung, antrags_nr, wahlperiode, betreff, status', 'required'),
			array('id, ba_nr', 'numerical', 'integerOnly' => true),
			array('typ', 'length', 'max' => 16),
			array('antrags_nr', 'length', 'max' => 20),
			array('referat', 'length', 'max' => 500),
			array('referent', 'length', 'max' => 200),
			array('wahlperiode, antrag_typ, status', 'length', 'max' => 50),
			array('bearbeitung', 'length', 'max' => 100),
			array('gestellt_am, bearbeitungsfrist, registriert_am, fristverlaengerung, initiative_to_aufgenommen', 'safe'),
			array('id, typ, datum_letzte_aenderung, ba_nr, gestellt_am, gestellt_von, antrags_nr, bearbeitungsfrist, registriert_am, referat, referent, wahlperiode, antrag_typ, betreff, kurzinfo, status, bearbeitung, fristverlaengerung, initiatorInnen, initiative_to_aufgenommen', 'safe', 'on' => 'insert'),

			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, typ, datum_letzte_aenderung, ba_nr, gestellt_am, gestellt_von, antrags_nr, bearbeitungsfrist, registriert_am, referat, referent, wahlperiode, antrag_typ, betreff, kurzinfo, status, bearbeitung, fristverlaengerung, initiatorInnen, initiative_to_aufgenommen', 'safe', 'on' => 'search'),
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
			'ba'               => array(self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'),
			'dokumente'        => array(self::HAS_MANY, 'AntragDokument', 'antrag_id'),
			'ergebnisse'       => array(self::HAS_MANY, 'AntragErgebnis', 'antrag_id'),
			//'antraege_links_in' => array(self::HAS_MANY, 'Antrag', 'antrag1'),
			//'antraege_links_out' => array(self::HAS_MANY, 'AntraegeLinks', 'antrag2'),
			'orte'             => array(self::HAS_MANY, 'AntragOrt', 'antrag_id'),
			'antraegePersonen' => array(self::HAS_MANY, 'AntragPerson', 'antrag_id'),
			'stadtraetInnen'   => array(self::MANY_MANY, 'StadtraetIn', 'antraege_stadtraetInnen(antrag_id, stadtraetIn_id)'),
			'vorlage2antraege' => array(self::MANY_MANY, 'Antrag', 'antraege_vorlagen(antrag1, antrag2)'),
			'antrag2vorlagen'  => array(self::MANY_MANY, 'Antrag', 'antraege_vorlagen(antrag2, antrag1)'),
			'abos'             => array(self::MANY_MANY, 'AntragAbo', 'antraege_abos(antrag_id, benutzerIn_id)'),
		);
	}


	/**
	 * @param Antrag $vorlage
	 */
	public function addVorlage($vorlage)
	{
		try {
			Yii::app()->db->createCommand()->insert("antraege_vorlagen", array("antrag1" => $vorlage->id, "antrag2" => $this->id));
			$this->antrag2vorlagen     = array_merge($this->antrag2vorlagen, array($vorlage));
			$vorlage->vorlage2antraege = array_merge($vorlage->vorlage2antraege, array($this));
		} catch (Exception $e) {
		}
	}

	/**
	 * @param Antrag $antrag
	 */
	public function addAntrag($antrag)
	{
		try {
			Yii::app()->db->createCommand()->insert("antraege_vorlagen", array("antrag2" => $antrag->id, "antrag1" => $this->id));
			$this->vorlage2antraege  = array_merge($this->vorlage2antraege, array($antrag));
			$antrag->antrag2vorlagen = array_merge($antrag->antrag2vorlagen, array($this));
		} catch (Exception $e) {
		}
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                        => 'ID',
			'typ'                       => 'Typ',
			'datum_letzte_aenderung'    => 'Datum Letzte Aenderung',
			'ba_nr'                     => 'Ba Nr',
			'gestellt_am'               => 'Gestellt Am',
			'gestellt_von'              => 'Gestellt Von',
			'antrags_nr'                => 'Antrags Nr',
			'bearbeitungsfrist'         => 'Bearbeitungsfrist',
			'registriert_am'            => 'Registriert Am',
			'referat'                   => 'Referat',
			'referent'                  => 'Referent',
			'wahlperiode'               => 'Wahlperiode',
			'antrag_typ'                => 'Antrag Typ',
			'betreff'                   => 'Betreff',
			'kurzinfo'                  => 'Kurzinfo',
			'status'                    => 'Status',
			'bearbeitung'               => 'Bearbeitung',
			'fristverlaengerung'        => 'Fristverlaengerung',
			'initiatorInnen'               => 'InitiatorInnen',
			'initiative_to_aufgenommen' => 'Initiative To Aufgenommen',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('typ', $this->typ, true);
		$criteria->compare('datum_letzte_aenderung', $this->datum_letzte_aenderung, true);
		$criteria->compare('ba_nr', $this->ba_nr);
		$criteria->compare('gestellt_am', $this->gestellt_am, true);
		$criteria->compare('gestellt_von', $this->gestellt_von, true);
		$criteria->compare('antrags_nr', $this->antrags_nr, true);
		$criteria->compare('bearbeitungsfrist', $this->bearbeitungsfrist, true);
		$criteria->compare('registriert_am', $this->registriert_am, true);
		$criteria->compare('referat', $this->referat, true);
		$criteria->compare('referent', $this->referent, true);
		$criteria->compare('wahlperiode', $this->wahlperiode, true);
		$criteria->compare('antrag_typ', $this->antrag_typ, true);
		$criteria->compare('betreff', $this->betreff, true);
		$criteria->compare('kurzinfo', $this->kurzinfo, true);
		$criteria->compare('status', $this->status, true);
		$criteria->compare('bearbeitung', $this->bearbeitung, true);
		$criteria->compare('fristverlaengerung', $this->fristverlaengerung, true);
		$criteria->compare('initiatorInnen', $this->initiatorInnen, true);
		$criteria->compare('initiative_to_aufgenommen', $this->initiative_to_aufgenommen, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}


	/**
	 * @param string $zeit_von
	 * @param string $zeit_bis
	 * @param int $limit
	 * @return $this
	 */
	public function neueste_stadtratsantragsdokumente($zeit_von, $zeit_bis, $limit = 0)
	{
		$params = array(
			'condition' => 'ba_nr IS NULL AND datum_letzte_aenderung >= "' . addslashes($zeit_von) . '" AND datum_letzte_aenderung <= "' . addslashes($zeit_bis) . '"',
			'order' => 'datum DESC',
			'with' => array(
				'dokumente' => array(
					'condition' => 'datum >= "' . addslashes($zeit_von) . '" AND datum <= "' . addslashes($zeit_bis) . '"',
				),
		));
		if ($limit > 0) $params['limit'] = $limit;
		$this->getDbCriteria()->mergeWith($params);
		return $this;
	}


	/**
	 * @throws CDbException|Exception
	 */
	public function copyToHistory()
	{
		$history = new AntragHistory();
		$history->setAttributes($this->getAttributes());
		try {
			if (!$history->save()) {
				if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "Antrag:moveToHistory Error", print_r($history->getErrors(), true));
				throw new Exception("Fehler");
			}
		} catch (CDbException $e) {
			if (strpos($e->getMessage(), "Duplicate entry") === false) throw $e;
		}

	}


	/**
	 * @throws Exception
	 */
	public function resetPersonen()
	{
		/** @var array|AntragPerson[] $alte */
		$alte = AntragPerson::model()->findAllByAttributes(array("antrag_id" => $this->id));
		foreach ($alte as $alt) $alt->delete();

		$indexed = array();

		$gestellt_von = RISTools::normalize_antragvon($this->gestellt_von);
		foreach ($gestellt_von as $x) if (!in_array($x["name_normalized"], $indexed)) {
			$indexed[]     = $x["name_normalized"];
			$person        = Person::getOrCreate($x["name"], $x["name_normalized"]);
			$ap            = new AntragPerson();
			$ap->antrag_id = $this->id;
			$ap->person_id = $person->id;
			$ap->typ       = AntragPerson::$TYP_GESTELLT_VON;
			if (!$ap->save()) {
				if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "Antrag:resetPersonen Error", print_r($ap->getErrors(), true));
				throw new Exception("Fehler");
			}
		}

		$initiatorInnen = RISTools::normalize_antragvon($this->initiatorInnen);
		foreach ($initiatorInnen as $x) if (!in_array($x["name_normalized"], $indexed)) {
			$indexed[]     = $x["name_normalized"];
			$person        = Person::getOrCreate($x["name"], $x["name_normalized"]);
			$ap            = new AntragPerson();
			$ap->antrag_id = $this->id;
			$ap->person_id = $person->id;
			$ap->typ       = AntragPerson::$TYP_INITIATORIN;
			if (!$ap->save()) {
				if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "Antrag:resetPersonen Error", print_r($ap->getErrors(), true));
				throw new Exception("Fehler");
			}
		}
	}

	/**
	 * @return string
	 */
	public function getLink()
	{
		return Yii::app()->createUrl("antraege/anzeigen",array("id" => $this->id));
	}

	/**
	 * @return string
	 */
	public function getSourceLink() {
		switch ($this->typ) {
			case Antrag::$TYP_BA_ANTRAG:
				return "http://www.ris-muenchen.de/RII2/BA-RII/ba_antraege_details.jsp?Id=" . $this->id . "&selTyp=BA-Antrag";
			case Antrag::$TYP_BA_INITIATIVE:
				return "http://www.ris-muenchen.de/RII2/BA-RII/ba_initiativen_details.jsp?Id=" . $this->id;
			case Antrag::$TYP_STADTRAT_ANTRAG:
				return "http://www.ris-muenchen.de/RII2/RII/ris_antrag_detail.jsp?risid=" . $this->id;
			case Antrag::$TYP_STADTRAT_VORLAGE:
				return "http://www.ris-muenchen.de/RII2/RII/ris_vorlagen_detail.jsp?risid=" . $this->id;
		}
		return "";
	}

	/** @return string */
	public function getTypName()
	{
		$str = explode("|", Antrag::$TYPEN_ALLE[$this->typ]);
		return $str[0];
	}

	/** @return string */
	public function getName()
	{
		return RISTools::korrigiereTitelZeichen($this->betreff);
	}
}