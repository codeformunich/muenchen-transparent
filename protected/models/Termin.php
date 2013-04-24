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
	public static function model($className=__CLASS__)
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
			array('id, termin_reihe, gremium_id, ba_nr, termin_prev_id, termin_next_id', 'numerical', 'integerOnly'=>true),
			array('referat, referent, vorsitz', 'length', 'max'=>200),
			array('wahlperiode', 'length', 'max'=>20),
			array('status', 'length', 'max'=>100),
			array('termin', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, datum_letzte_aenderung, termin_reihe, gremium_id, ba_nr, termin, termin_prev_id, termin_next_id, sitzungsort, referat, referent, vorsitz, wahlperiode, status', 'safe', 'on'=>'search'),
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
			'antraegeDokumente' => array(self::HAS_MANY, 'AntragDokument', 'termin_id'),
			'antraegeErgebnisse' => array(self::HAS_MANY, 'AntragErgebnis', 'sitzungstermin_id'),
			'antraegeOrte' => array(self::HAS_MANY, 'AntragOrt', 'termin_id'),
			'gremium' => array(self::BELONGS_TO, 'Gremium', 'gremium_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'datum_letzte_aenderung' => 'Datum Letzte Aenderung',
			'termin_reihe' => 'Termin Reihe',
			'gremium_id' => 'Gremium',
			'ba_nr' => 'Ba Nr',
			'termin' => 'Termin',
			'termin_prev_id' => 'Termin Prev',
			'termin_next_id' => 'Termin Next',
			'sitzungsort' => 'Sitzungsort',
			'referat' => 'Referat',
			'referent' => 'Referent',
			'vorsitz' => 'Vorsitz',
			'wahlperiode' => 'Wahlperiode',
			'status' => 'Status',
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

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('datum_letzte_aenderung',$this->datum_letzte_aenderung,true);
		$criteria->compare('termin_reihe',$this->termin_reihe);
		$criteria->compare('gremium_id',$this->gremium_id);
		$criteria->compare('ba_nr',$this->ba_nr);
		$criteria->compare('termin',$this->termin,true);
		$criteria->compare('termin_prev_id',$this->termin_prev_id);
		$criteria->compare('termin_next_id',$this->termin_next_id);
		$criteria->compare('sitzungsort',$this->sitzungsort,true);
		$criteria->compare('referat',$this->referat,true);
		$criteria->compare('referent',$this->referent,true);
		$criteria->compare('vorsitz',$this->vorsitz,true);
		$criteria->compare('wahlperiode',$this->wahlperiode,true);
		$criteria->compare('status',$this->status,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}




	/**
	 * @throws CDbException|Exception
	 */
	public function copyToHistory()
	{
		$history = new TerminHistory();
		$history->setAttributes($this->getAttributes());
		if ($history->wahlperiode == "") $history->wahlperiode = "?";
		if ($history->status == "") $history->status = "?";
		if ($history->sitzungsort == "") $history->sitzungsort = "?";
		try {
			if (!$history->save()) {
				if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "Termin:moveToHistory Error", print_r($history->getErrors(), true));
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
		return Yii::app()->createUrl("termin/anzeigen",array("id" => $this->id));
	}


	/** @return string */
	public function getTypName()
	{
		if ($this->ba_nr > 0) return "BA-Termin";
		else return "Stadtratstermin";
	}

	/** @return string */
	public function getName()
	{
		return $this->gremium->name . " (" . $this->termin . ")";
	}
}