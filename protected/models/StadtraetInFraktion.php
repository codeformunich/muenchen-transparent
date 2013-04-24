<?php

/**
 * @property integer $stadtraetIn_id
 * @property integer $fraktion_id
 * @property string $wahlperiode
 * @property string $mitgliedschaft
 * @property string $funktion
 *
 * The followings are the available model relations:
 * @property Fraktion $fraktion
 * @property StadtraetIn $stadtraetIn
 */
class StadtraetInFraktion extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StadtraetInFraktion the static model class
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
		return 'stadtraetInnen_fraktionen';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('stadtraetIn_id, fraktion_id, wahlperiode, mitgliedschaft', 'required'),
			array('stadtraetIn_id, fraktion_id', 'numerical', 'integerOnly' => true),
			array('wahlperiode', 'length', 'max' => 30),
			array('funktion', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('stadtraetIn_id, fraktion_id, wahlperiode, mitgliedschaft, funktion', 'safe', 'on' => 'search'),
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
			'fraktion'    => array(self::BELONGS_TO, 'Fraktion', 'fraktion_id'),
			'stadtraetIn' => array(self::BELONGS_TO, 'StadtraetIn', 'stadtraetIn_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'stadtraetIn_id' => 'StadträtIn',
			'fraktion_id'    => 'Fraktion',
			'wahlperiode'    => 'Wahlperiode',
			'mitgliedschaft' => 'Mitgliedschaft',
			'funktion'       => 'Funktion',
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

		$criteria->compare('stadtraetIn_id', $this->stadtraetIn_id);
		$criteria->compare('fraktion_id', $this->fraktion_id);
		$criteria->compare('wahlperiode', $this->wahlperiode, true);
		$criteria->compare('mitgliedschaft', $this->mitgliedschaft, true);
		$criteria->compare('funktion', $this->funktion, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}