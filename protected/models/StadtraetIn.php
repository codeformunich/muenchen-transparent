<?php

/**
 * @property integer $id
 * @property string $gewaehlt_am
 * @property string $bio
 * @property string $web
 * @property string $name
 *
 * The followings are the available model relations:
 * @property Antrag[] $antraege
 * @property Person[] $personen
 * @property StadtraetInFraktion[] $stadtraetInnenFraktionen
 */
class StadtraetIn extends CActiveRecord implements IRISItem
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StadtraetIn the static model class
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
		return 'stadtraetInnen';
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
			array('id', 'numerical', 'integerOnly'=>true),
			array('web', 'length', 'max'=>250),
			array('name', 'length', 'max'=>100),
			array('gewaehlt_am', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, gewaehlt_am, bio, web, name', 'safe', 'on'=>'search'),
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
			'antraege' => array(self::MANY_MANY, 'Antrag', 'antraege_stadtraetInnen(stadtraetIn_id, antraege_id)'),
			'personen' => array(self::HAS_MANY, 'Person', 'ris_stadtraetIn'),
			'stadtraetInnenFraktionen' => array(self::HAS_MANY, 'StadtraetInFraktion', 'stadtraetIn_id', 'order' => 'wahlperiode DESC'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'gewaehlt_am' => 'Gewaehlt Am',
			'bio' => 'Bio',
			'web' => 'Web',
			'name' => 'Name',
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
		$criteria->compare('gewaehlt_am',$this->gewaehlt_am,true);
		$criteria->compare('bio',$this->bio,true);
		$criteria->compare('web',$this->web,true);
		$criteria->compare('name',$this->name,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * @return string
	 */
	public function getLink()
	{
		return Yii::app()->createUrl("stadtraetIn/anzeigen",array("id" => $this->id));
	}


	/** @return string */
	public function getTypName()
	{
		return "Stadtratsmitglied";
	}

	/** @return string */
	public function getName()
	{
		return $this->name;
	}
}