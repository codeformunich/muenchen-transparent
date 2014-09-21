<?php

/**
 * The followings are the available columns in table 'gremien':
 * @property integer $id
 * @property string $name
 * @property string $urlpart
 * @property string $strasse
 * @property string $ort
 * @property string $plz
 * @property string $email
 * @property string $telefon
 * @property string $kurzbeschreibung
 * @property integer $aktiv
 *
 * The followings are the available model relations:
 * @property Antrag[] $antraege
 */
class Referat extends CActiveRecord implements IRISItem
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Referat the static model class
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
		return 'referate';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, name, urlpart', 'required'),
			array('id, aktiv', 'numerical', 'integerOnly' => true),
			array('name, email, telefon', 'length', 'max' => 100),
			array('kurzbeschreibung', 'length', 'max' => 200),
			array('strasse, urlpart', 'length', 'max' => 45),
			array('plz', 'length', 'max' => 10),
			array('ort', 'length', 'max' => 30),
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
			'antraege' => array(self::HAS_MANY, 'Antrag', 'referat_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'               => 'ID',
			'name'             => 'Name',
			'urlpart'          => 'URL',
			'plz'              => 'PLZ',
			'ort'              => 'Ort',
			'strasse'          => 'Straße',
			'email'            => 'E-Mail',
			'telefon'          => 'Telefonnummer',
			'kurzbeschreibung' => 'Kurzbeschreibung',
			'aktiv'            => 'Aktiv',
		);
	}

	/**
	 * @return string
	 */
	public function getLink()
	{
		return Yii::app()->createUrl("themen/referat", array("id" => $this->id));
	}


	/** @return string */
	public function getTypName()
	{
		return "Referat";
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



	/**
	 * @param string $name
	 * @return null|Referat
	 */
	public static function getByHtmlName($name)
	{
		$name = trim(strip_tags($name));
		$ref  = Referat::model()->findByAttributes(array("name" => $name));
		return $ref;
	}


	/**
	 * @param int $referat_id
	 * @param string $zeit_von
	 * @param string $zeit_bis
	 * @param int $limit
	 * @return $this
	 */
	public function neueste_dokumente($referat_id, $zeit_von = "", $zeit_bis = "", $limit = 0)
	{
		$time_condition = '';
		if ($zeit_von != "") $time_condition .= 'c.datum >= "' . addslashes($zeit_von) . '"';
		if ($zeit_von != "" && $zeit_bis != "") $time_condition .= ' AND ';
		if ($zeit_bis != "") $time_condition .= 'c.datum <= "' . addslashes($zeit_bis) . '"';

		$params = array(
			'alias'     => 'a',
			'condition' => 'a.id = ' . IntVal($referat_id),
			'order'     => 'c.datum DESC',
			'with'      => array(
				'antraege'           => array(
					'alias' => 'b',
				),
				'antraege.dokumente' => array(
					'alias'     => 'c',
					'condition' => $time_condition,
				),
			));
		if ($limit > 0) $params['limit'] = $limit;
		$this->getDbCriteria()->mergeWith($params);
		return $this;
	}
}