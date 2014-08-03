<?php

/**
 * This is the model class for table "termine_history".
 *
 * The followings are the available columns in table 'termine_history':
 * @property integer $id
 * @property integer $vorgang_id
 * @property string $datum_letzte_aenderung
 * @property integer $termin_reihe
 * @property integer $gremium_id
 * @property integer $ba_nr
 * @property string $termin
 * @property integer $termin_prev_id
 * @property integer $termin_next_id
 * @property string $sitzungsort
 * @property string $referat
 * @property string $referent
 * @property string $vorsitz
 * @property string $wahlperiode
 * @property string $status
 */
class TerminHistory extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return TerminHistory the static model class
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
		return 'termine_history';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, datum_letzte_aenderung', 'required'),
			array('id, termin_reihe, gremium_id, ba_nr, termin_prev_id, termin_next_id, vorgang_id', 'numerical', 'integerOnly'=>true),
			array('referat, referent, vorsitz', 'length', 'max'=>200),
			array('wahlperiode', 'length', 'max'=>20),
			array('status', 'length', 'max'=>100),
			array('termin', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, datum_letzte_aenderung, termin_reihe, gremium_id, ba_nr, termin, termin_prev_id, termin_next_id, sitzungsort, referat, referent, vorsitz, wahlperiode, status', 'safe', 'on'=>'search'),
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
			'vorgang_id' => 'Vorgangs-ID',
			'datum_letzte_aenderung' => 'Datum Letzte Aenderung',
			'termin_reihe' => 'Termin Reihe',
			'gremium_id' => 'Gremium',
			'ba_nr' => 'Ba Nr',
			'termin' => 'Termin',
			'termin_prev_id' => 'Termin Prev',
			'termin_next_id' => 'Termin Next',
			'sitzungsort' => 'Sitzungsort',
			'referat' => 'Referat',
			'referent' => 'Referent',
			'vorsitz' => 'Vorsitz',
			'wahlperiode' => 'Wahlperiode',
			'status' => 'Status',
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
		$criteria->compare('datum_letzte_aenderung',$this->datum_letzte_aenderung,true);
		$criteria->compare('termin_reihe',$this->termin_reihe);
		$criteria->compare('gremium_id',$this->gremium_id);
		$criteria->compare('ba_nr',$this->ba_nr);
		$criteria->compare('termin',$this->termin,true);
		$criteria->compare('termin_prev_id',$this->termin_prev_id);
		$criteria->compare('termin_next_id',$this->termin_next_id);
		$criteria->compare('sitzungsort',$this->sitzungsort,true);
		$criteria->compare('referat',$this->referat,true);
		$criteria->compare('referent',$this->referent,true);
		$criteria->compare('vorsitz',$this->vorsitz,true);
		$criteria->compare('wahlperiode',$this->wahlperiode,true);
		$criteria->compare('status',$this->status,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}