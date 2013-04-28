<?php

/**
 * @property integer $antrag_id
 * @property integer $benutzerIn_id
 *
 * @property Antrag $antrag
 * @property BenutzerIn $benutzerIn
 */
class AntragAbo extends CActiveRecord
{

	/**
	 * @param string $className active record class name.
	 * @return AntragAbo the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string
	 */
	public function tableName()
	{
		return 'antraege_abos';
	}


	/**
	 * @return array
	 */
	public function rules()
	{
		$rules = array(
			array('antrag_id, benutzerIn_id', 'required'),
			array('antrag_id, benutzerIn_id', 'numerical', 'integerOnly' => true),
		);
		return $rules;
	}

	/**
	 * @return array
	 */
	public function relations()
	{
		return array(
			'antrag'     => array(self::BELONGS_TO, 'Antrag', 'antrag_id'),
			'benutzerIn' => array(self::BELONGS_TO, 'BenutzerIn', 'benutzerIn_id'),
		);
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
			'antrag_id'     => Yii::t('app', 'Antrag-ID'),
			'benutzerIn_id' => Yii::t('app', 'BenutzerInnen-ID'),
			'antrag'        => Yii::t('app', 'Antrag'),
			'benutzerIn'    => Yii::t('app', 'BenutzerIn'),
		);
	}

	/**
	 * @param int $n
	 * @return string
	 */
	public static function label($n = 1)
	{
		return Yii::t('app', 'AntragsAbo|AntragsAbos', $n);
	}

}