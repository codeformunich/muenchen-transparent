<?php

/**
 * This is the model class for table "antraege_history".
 *
 * The followings are the available columns in table 'antraege_history':
 * @property integer $id
 * @property string $typ
 * @property string $datum_letzte_aenderung
 * @property integer $ba_nr
 * @property string $gestellt_am
 * @property string $gestellt_von
 * @property string $antrags_nr
 * @property string $bearbeitungsfrist
 * @property string $registriert_am
 * @property string $erledigt_am
 * @property string $referat
 * @property string $referent
 * @property string $wahlperiode
 * @property string $antrag_typ
 * @property string $betreff
 * @property string $kurzinfo
 * @property string $status
 * @property string $bearbeitung
 * @property string $fristverlaengerung
 * @property string $initiatoren
 * @property string $initiative_to_aufgenommen
 *
 * The followings are the available model relations:
 * @property Bezirksausschuss $ba
 */
class AntragHistory extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AntragHistory the static model class
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
		return 'antraege_history';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, typ, datum_letzte_aenderung', 'required'),
			array('id, ba_nr', 'numerical', 'integerOnly' => true),
			array('typ', 'length', 'max' => 16),
			array('antrags_nr', 'length', 'max' => 20),
			array('referat', 'length', 'max' => 500),
			array('referent', 'length', 'max' => 200),
			array('wahlperiode, antrag_typ, status', 'length', 'max' => 50),
			array('bearbeitung', 'length', 'max' => 100),
			array('gestellt_am, bearbeitungsfrist, registriert_am, erledigt_am, fristverlaengerung, initiative_to_aufgenommen', 'safe'),
			array('id, typ, datum_letzte_aenderung, ba_nr, gestellt_am, gestellt_von, antrags_nr, bearbeitungsfrist, registriert_am, erledigt_am, referat, referent, wahlperiode, antrag_typ, betreff, kurzinfo, status, bearbeitung, fristverlaengerung, initiatoren, initiative_to_aufgenommen', 'safe', 'on' => 'insert'),

			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, typ, datum_letzte_aenderung, ba_nr, gestellt_am, gestellt_von, antrags_nr, bearbeitungsfrist, registriert_am, erledigt_am, referat, referent, wahlperiode, antrag_typ, betreff, kurzinfo, status, bearbeitung, fristverlaengerung, initiatoren, initiative_to_aufgenommen', 'safe', 'on' => 'search'),
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
			'ba' => array(self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'),
		);
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
			'erledigt_am'               => 'Erledigt Am',
			'referat'                   => 'Referat',
			'referent'                  => 'Referent',
			'wahlperiode'               => 'Wahlperiode',
			'antrag_typ'                => 'Antrag Typ',
			'betreff'                   => 'Betreff',
			'kurzinfo'                  => 'Kurzinfo',
			'status'                    => 'Status',
			'bearbeitung'               => 'Bearbeitung',
			'fristverlaengerung'        => 'Fristverlaengerung',
			'initiatoren'               => 'Initiatoren',
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
		$criteria->compare('erledigt_am', $this->erledigt_am, true);
		$criteria->compare('referat', $this->referat, true);
		$criteria->compare('referent', $this->referent, true);
		$criteria->compare('wahlperiode', $this->wahlperiode, true);
		$criteria->compare('antrag_typ', $this->antrag_typ, true);
		$criteria->compare('betreff', $this->betreff, true);
		$criteria->compare('kurzinfo', $this->kurzinfo, true);
		$criteria->compare('status', $this->status, true);
		$criteria->compare('bearbeitung', $this->bearbeitung, true);
		$criteria->compare('fristverlaengerung', $this->fristverlaengerung, true);
		$criteria->compare('initiatoren', $this->initiatoren, true);
		$criteria->compare('initiative_to_aufgenommen', $this->initiative_to_aufgenommen, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}