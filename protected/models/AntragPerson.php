<?php

/**
 * This is the model class for table "antraege_personen".
 *
 * The followings are the available columns in table 'antraege_personen':
 * @property integer $antrag_id
 * @property integer $person_id
 * @property string $typ
 *
 * @property Person $person
 * @property Antrag $antrag
 */
class AntragPerson extends CActiveRecord
{

	public static $TYP_GESTELLT_VON = "gestellt_von";
	public static $TYP_INITIATORIN = "initiator";
	public static $TYPEN_ALLE = array(
		"gestellt_von" => "Gestellt von",
		"initiator"    => "InitiatorIn"
	);


	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AntragPerson the static model class
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
		return 'antraege_personen';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('antrag_id, person_id', 'required'),
			array('antrag_id, person_id', 'numerical', 'integerOnly' => true),
			array('typ', 'length', 'max' => 12),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'person' => array(self::BELONGS_TO, 'Person', 'person_id'),
			'antrag' => array(self::BELONGS_TO, 'Antrag', 'antrag_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'antrag_id' => 'Antrag (ID)',
			'person_id' => 'Person (ID)',
			'antrag'    => 'Antrag',
			'person'    => 'Person',
			'typ'       => 'Typ',
		);
	}
}