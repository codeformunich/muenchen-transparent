<?php

/**
 * This is the model class for table "fraktionen".
 *
 * The followings are the available columns in table 'fraktionen':
 * @property integer $id
 * @property string $name
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
	public static function model($className=__CLASS__)
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
			array('id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>50),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
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
			'personen' => array(self::HAS_MANY, 'Person', 'ris_fraktion'),
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

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
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

	/** @return string */
	public function getName()
	{
		return $this->name;
	}
}