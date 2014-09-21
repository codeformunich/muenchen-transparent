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
 * @property integer $ris_fraktion
 *
 * The followings are the available model relations:
 * @property AntragPerson[] $antraegePersonen
 * @property StadtraetIn $stadtraetIn
 * @property Fraktion $fraktion
 */
class Person extends CActiveRecord implements IRISItem
{

	public static $TYP_SONSTIGES = "sonstiges";
	public static $TYP_PERSON = "person";
	public static $TYP_FRAKTION = "fraktion";
	public static $TYPEN_ALLE = array(
		"sonstiges" => "Sonstiges / Unbekannt",
		"person"    => "Person",
		"fraktion"  => "Fraktion"
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Person the static model class
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
			array('ris_stadtraetIn, ris_fraktion', 'numerical', 'integerOnly' => true),
			array('typ', 'length', 'max' => 9),
			array('name, name_normalized', 'length', 'max' => 100),
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
			'stadtraetIn'      => array(self::BELONGS_TO, 'StadtraetIn', 'ris_stadtraetIn'),
			'fraktion'         => array(self::BELONGS_TO, 'Fraktion', 'ris_fraktion'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'              => 'ID',
			'name_normalized' => 'Name Normalized',
			'typ'             => 'Typ',
			'name'            => 'Name',
			'ris_stadtraetIn' => 'StadträtInnen-ID',
			'ris_fraktion'    => 'Fraktion',
		);
	}

	/**
	 * @param string $name
	 * @param string $name_normalized
	 * @return Person
	 * @throws Exception
	 */
	public static function getOrCreate($name, $name_normalized)
	{
		/** @var Person|null $pers */
		$pers = Person::model()->findByAttributes(array("name_normalized" => $name_normalized));
		if (is_null($pers)) {
			$pers                  = new Person();
			$pers->name            = $name;
			$pers->name_normalized = $name_normalized;
			$pers->typ             = static::$TYP_SONSTIGES;
			if (!$pers->save()) {
				RISTools::send_email(Yii::app()->params['adminEmail'], "Person:getOrCreate Error", print_r($pers->getErrors(), true));
				throw new Exception("Fehler beim Speichern: Person");
			}
		}
		return $pers;
	}

	/**
	 * @param string $datum
	 * @return string|null
	 */
	public function ratePartei($datum = "")
	{
		if (isset($this->fraktion) && $this->fraktion) return $this->fraktion->name;
		if (!isset($this->stadtraetIn) || is_null($this->stadtraetIn)) return null;
		if (!isset($this->stadtraetIn->stadtraetInnenFraktionen[0]->fraktion)) return null;
		if ($datum != "") foreach ($this->stadtraetIn->stadtraetInnenFraktionen as $fraktionsZ) {
			$dat = str_replace("-", "", $datum);
			if ($dat >= str_replace("-", "", $fraktionsZ->datum_von) && (is_null($fraktionsZ->datum_bis) || $dat <= str_replace("-", "", $fraktionsZ->datum_bis))) return $fraktionsZ->fraktion->name;
		}
		return $this->stadtraetIn->stadtraetInnenFraktionen[0]->fraktion->name;
	}

	/** @return string */
	public function getLink()
	{
		return "http://www.ris-muenchen.de/RII2/RII/ris_mitglieder_detail.jsp?risid=" . $this->id;
	}

	/** @return string */
	public function getTypName()
	{
		return "Stadtratsmitglied";
	}

	/**
	 * @param bool $kurzfassung
	 * @return string
	 */
	public function getName($kurzfassung = false)
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDate() {
		return "0000-00-00 00:00:00";
	}


}