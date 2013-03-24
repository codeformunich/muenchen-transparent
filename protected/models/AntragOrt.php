<?php

/**
 * This is the model class for table "antraege_orte".
 *
 * The followings are the available columns in table 'antraege_orte':
 * @property integer $id
 * @property integer $antrag_id
 * @property integer $termin_id
 * @property integer $dokument_id
 * @property string $ort_name
 * @property integer $ort_id
 * @property string $source
 * @property string $datum
 *
 * The followings are the available model relations:
 * @property AntragDokument $dokument
 * @property Antrag $antrag
 * @property Termin $termin
 * @property OrtGeo $ort
 */
class AntragOrt extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AntragOrt the static model class
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
		return 'antraege_orte';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('dokument_id, ort_name, ort_id, source, datum', 'required'),
			array('antrag_id, termin_id, dokument_id, ort_id', 'numerical', 'integerOnly'=>true),
			array('ort_name', 'length', 'max'=>100),
			array('source', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, antrag_id, termin_id, dokument_id, ort_name, ort_id, source, datum', 'safe', 'on'=>'search'),
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
			'dokument' => array(self::BELONGS_TO, 'AntragDokument', 'dokument_id'),
			'antrag' => array(self::BELONGS_TO, 'Antrag', 'antrag_id'),
			'termin' => array(self::BELONGS_TO, 'Dokument', 'termin_id'),
			'ort' => array(self::BELONGS_TO, 'OrtGeo', 'ort_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'antrag_id' => 'Antrag',
			'termin_id' => 'Termin',
			'dokument_id' => 'Dokument',
			'ort_name' => 'Ort Name',
			'ort_id' => 'Ort',
			'source' => 'Source',
			'datum' => 'Datum',
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
		$criteria->compare('antrag_id',$this->antrag_id);
		$criteria->compare('termin_id',$this->termin_id);
		$criteria->compare('dokument_id',$this->dokument_id);
		$criteria->compare('ort_name',$this->ort_name,true);
		$criteria->compare('ort_id',$this->ort_id);
		$criteria->compare('source',$this->source,true);
		$criteria->compare('datum',$this->datum,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}