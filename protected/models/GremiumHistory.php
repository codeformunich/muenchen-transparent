<?php

/**
 * This is the model class for table "gremien_history".
 *
 * The followings are the available columns in table 'gremien_history':
 * @property integer $id
 * @property string $datum_letzte_aenderung
 * @property integer $ba_nr
 * @property string $name
 * @property string $kuerzel
 * @property string $gremientyp
 * @property string $referat
 *
 * The followings are the available model relations:
 * @property Bezirksausschuss $ba
 */
class GremiumHistory extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return GremiumHistory the static model class
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
		return 'gremien_history';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, datum_letzte_aenderung, name, kuerzel, gremientyp, referat', 'required'),
			array('id, ba_nr', 'numerical', 'integerOnly' => true),
			array('name, gremientyp, referat', 'length', 'max' => 100),
			array('kuerzel', 'length', 'max' => 20),
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
			'ba' => array(self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                     => 'ID',
			'datum_letzte_aenderung' => 'Datum Letzte Aenderung',
			'ba_nr'                  => 'Ba Nr',
			'name'                   => 'Name',
			'kuerzel'                => 'Kuerzel',
			'gremientyp'             => 'Gremientyp',
			'referat'                => 'Referat',
		);
	}
}