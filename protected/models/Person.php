<?php

/**
 * This is the model class for table "personen".
 *
 * The followings are the available columns in table 'personen':
 * @property integer $id
 * @property string $name_normalized
 * @property string $typ
 * @property string $name
 * @property integer $ris_stadtraetIn
 *
 * The followings are the available model relations:
 * @property AntragPerson[] $antraegePersonen
 * @property StadtraetIn $stadtraetIn
 */
class Person extends CActiveRecord
{

	public static $TYP_SONSTIGES = "sonstiges";
	public static $TYP_PERSON = "person";
	public static $TYP_FRAKTION = "fraktion";
	public static $TYPEN_ALLE = array(
		"sonstiges" => "Sonstiges / Unbekannt",
		"person" => "Person",
		"fraktion" => "Fraktion"
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Person the static model class
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
		return 'personen';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name_normalized, typ, name', 'required'),
			array('ris_stadtraetIn', 'numerical', 'integerOnly'=>true),
			array('name_normalized', 'length', 'max'=>50),
			array('typ', 'length', 'max'=>9),
			array('name', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name_normalized, typ, name, ris_stadtraetIn', 'safe', 'on'=>'search'),
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
			'antraegePersonen' => array(self::HAS_MANY, 'AntragPerson', 'person_id'),
			'stadtraetIn' => array(self::BELONGS_TO, 'StadtraetIn', 'ris_stadtraetIn'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name_normalized' => 'Name Normalized',
			'typ' => 'Typ',
			'name' => 'Name',
			'stadtraetIn' => 'StadträtInnen-ID',
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
		$criteria->compare('name_normalized',$this->name_normalized,true);
		$criteria->compare('typ',$this->typ,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('ris_stadtraetIn',$this->ris_stadtraetIn);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * @param string $name
	 * @param string $name_normalized
	 * @return Person
	 * @throws Exception
	 */
	public static function getOrCreate ($name, $name_normalized) {
		/** @var Person|null $pers */
		$pers = Person::model()->findByAttributes(array("name_normalized" => $name_normalized));
		if (is_null($pers)) {
			$pers = new Person();
			$pers->name = $name;
			$pers->name_normalized = $name_normalized;
			$pers->typ = static::$TYP_SONSTIGES;
			if (!$pers->save()) {
				if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "Person:getOrCreate Error", print_r($pers->getErrors(), true));
				throw new Exception("Fehler beim Speichern: Person");
			}
		}
		return $pers;
	}

	/**
	 * @return string|null
	 */
	public function ratePartei() {
		if (!isset($this->stadtraetIn) || is_null($this->stadtraetIn)) return null;
		if (!isset($this->stadtraetIn->stadtraetInnenFraktionen[0]->fraktion)) return null;
		return $this->stadtraetIn->stadtraetInnenFraktionen[0]->fraktion->name;
	}
}