<?php

/**
 * This is the model class for table "texte".
 *
 * The followings are the available columns in table 'texte':
 * @property int $id
 * @property string $name
 * @property int $angelegt_benutzerIn_id
 * @property string $angelegt_benutzerIn
 * @property string $angelegt_datum
 * @property int $reviewed
 *
 * The followings are the available model relations:
 * @property BenutzerIn $angelegt_benutzerIn
 * @property Antrag[] $antraege
 */
class Tag extends CActiveRecord
{

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Text the static model class
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
		return 'tags';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('id, angelegt_benutzerIn_id, reviewed', 'numerical', 'integerOnly' => true),
			array('name', 'length', 'max' => 100),
			array('angelegt_datum', 'length', 'max' => 20),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'angelegt_benutzerIn' => array(self::BELONGS_TO, 'BenutzerIn', 'angelegt_benutzerIn_id'),
			'antraege'            => array(self::MANY_MANY, 'Antrag', 'antraege_tags(tag_id, antrag_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'name'                   => 'Name',
			'reviewed'               => 'Geprüft',
			'angelegt_datum'         => 'Angelegt: Datum',
			'angelegt_benutzerIn_id' => 'Anegelegt: BenutzerIn-ID',
			'angelegt_benutzerIn'    => 'Anegelegt: BenutzerIn',
			'antraege'               => 'Anträge',
		);
	}

	/**
	 * @return string
	 */
	public function getNameLink() {
		return CHtml::encode($this->name); // @TODO
	}

}