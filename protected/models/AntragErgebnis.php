<?php

/**
 * This is the model class for table "antraege_ergebnisse".
 *
 * The followings are the available columns in table 'antraege_ergebnisse':
 * @property integer $id
 * @property string $datum_letzte_aenderung
 * @property integer $antrag_id
 * @property string $gremium_name
 * @property integer $gremium_id
 * @property integer $sitzungstermin_id
 * @property string $sitzungstermin_datum
 * @property string $beschluss_text
 * @property string $entscheidung
 * @property string $top_nr
 * @property string $top_betreff
 * @property string $status
 *
 * The followings are the available model relations:
 * @property Termin $sitzungstermin
 * @property Gremium $gremium
 * @property Antrag $antrag
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
			array('antrag_id, gremium_id, sitzungstermin_id', 'numerical', 'integerOnly' => true),
			array('gremium_name', 'length', 'max' => 100),
			array('beschluss_text', 'length', 'max' => 500),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, antrag_id, gremium_name, gremium_id, sitzungstermin_id, sitzungstermin_datum, beschluss_text, entscheidung, datum_letzte_aenderung', 'safe', 'on' => 'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'antrag_id'              => 'Antrag',
			'gremium_name'           => 'Gremium Name',
			'gremium_id'             => 'Gremium',
			'sitzungstermin_id'      => 'Sitzungstermin',
			'sitzungstermin_datum'   => 'Sitzungstermin Datum',
			'beschluss_text'         => 'Beschluss',
			'entscheidung'           => 'Entscheidung',
			'datum_letzte_aenderung' => 'Letzte Änderung',
			'top_nr'                 => 'Tagesordnungspunkt',
			'status'                 => 'Status'
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
		$criteria->compare('antrag_id', $this->antrag_id);
		$criteria->compare('gremium_name', $this->gremium_name, true);
		$criteria->compare('gremium_id', $this->gremium_id);
		$criteria->compare('sitzungstermin_id', $this->sitzungstermin_id);
		$criteria->compare('sitzungstermin_datum', $this->sitzungstermin_datum, true);
		$criteria->compare('beschluss_text', $this->beschluss_text, true);
		$criteria->compare('entscheidung', $this->entscheidung, true);
		$criteria->compare('datum_letzte_aenderung', $this->datum_letzte_aenderung, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}


	/**
	 * @throws CDbException|Exception
	 */
	public function copyToHistory()
	{
		$history = new AntragErgebnisHistory();
		$history->setAttributes($this->getAttributes());
		try {
			if (!$history->save()) {
				if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "AntragErgebnisHistory:moveToHistory Error", print_r($history->getErrors(), true));
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
		return Yii::app()->createUrl("beschluss/anzeigen", array("id" => $this->id));
	}


	/** @return string */
	public function getTypName()
	{
		return "Stadtratsbeschluss";
	}

	/** @return string */
	public function getName()
	{
		return $this->top_betreff;
	}
}