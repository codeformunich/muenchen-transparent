<?php

/**
 * This is the model class for table "strassen".
 *
 * The followings are the available columns in table 'strassen':
 * @property integer $id
 * @property string $name
 * @property string $plz
 * @property string $osm_ref
 */
class Strasse extends CActiveRecord
{

	/** @var string */
	public $name_normalized;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Strasse the static model class
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
		return 'strassen';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, plz, osm_ref', 'required'),
			array('name', 'length', 'max'=>100),
			array('plz, osm_ref', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, plz, osm_ref', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'plz' => 'Plz',
			'osm_ref' => 'Osm Ref',
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('plz',$this->plz,true);
		$criteria->compare('osm_ref',$this->osm_ref,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}