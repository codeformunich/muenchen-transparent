<?php

/**
 * This is the model class for table "bezirksausschuesse".
 *
 * The followings are the available columns in table 'bezirksausschuesse':
 * @property integer $id
 * @property integer $typ
 * @property string $betreff
 *
 * The followings are the available model relations:
 * @property Antrag[] $antraege
 * @property AntragErgebnis[] $ergebnisse
 * @propery AntragDokument[] $dokumente
 */
class Vorgang extends CActiveRecord
{
	/**
	 * @param string $className active record class name.
	 * @return Vorgang the static model class
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
		return 'vorgaenge';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, typ', 'required'),
			array('id, typ', 'numerical', 'integerOnly' => true),
			array('betreff', 'length', 'max' => 200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, typ, betreff', 'safe', 'on' => 'search'),
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
			'antraege'  => array(self::HAS_MANY, 'Antrag', 'vorgang_id'),
			'termine'   => array(self::HAS_MANY, 'Termin', 'vorgang_id'),
			'dokumente' => array(self::HAS_MANY, 'AntragDokument', 'vorgang_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'      => 'ID',
			'typ'     => 'Typ',
			'betreff' => 'Betreff',
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
		$criteria->compare('typ', $this->typ);
		$criteria->compare('betreff', $this->betreff, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}

}