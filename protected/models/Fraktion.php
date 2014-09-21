<?php

/**
 * This is the model class for table "fraktionen".
 *
 * The followings are the available columns in table 'fraktionen':
 * @property integer $id
 * @property string $name
 * @property integer $ba_nr
 * @property string $website
 *
 * The followings are the available model relations:
 * @property StadtraetInFraktion[] $stadtraetInnenFraktionen
 * @property Person[] $personen
 */
class Fraktion extends CActiveRecord implements IRISItem
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Fraktion the static model class
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
		return 'fraktionen';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, name', 'required'),
			array('id, ba_nr', 'numerical', 'integerOnly' => true),
			array('name', 'length', 'max' => 70),
			array('website', 'length', 'max' => 250),
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
			'stadtraetInnenFraktionen' => array(self::HAS_MANY, 'StadtraetInFraktion', 'fraktion_id'),
			'bezirksausschuss'         => array(self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'),
			'personen'                 => array(self::HAS_MANY, 'Person', 'ris_fraktion'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'    => 'ID',
			'ba_nr' => "BA-Nr",
			'name'  => 'Name',
		);
	}

	/** @return string */
	public function getLink()
	{
		$strs = $this->stadtraetInnenFraktionen;
		return "http://www.ris-muenchen.de/RII2/RII/ris_fraktionen_detail.jsp?risid=" . $this->id . "&periodeid=" . $strs[0]->wahlperiode;
	}

	/** @return string */
	public function getTypName()
	{
		return "Fraktion";
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