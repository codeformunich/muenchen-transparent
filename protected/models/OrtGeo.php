<?php

/**
 * This is the model class for table "orte_geo".
 *
 * The followings are the available columns in table 'orte_geo':
 * @property integer $id
 * @property string $ort
 * @property float $lat
 * @property float $lon
 * @property string $source
 * @property integer $to_hide
 * @property string $to_hide_kommentar
 * @property string $datum
 *
 * The followings are the available model relations:
 * @property AntragOrt[] $antraegeOrte
 */
class OrtGeo extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return OrtGeo the static model class
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
		return 'orte_geo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ort, lat, lon, source, to_hide, datum', 'required'),
			array('to_hide', 'numerical', 'integerOnly' => true),
			array('lat, lon', 'numerical'),
			array('ort', 'length', 'max' => 100),
			array('source', 'length', 'max' => 6),
			array('to_hide_kommentar', 'length', 'max' => 200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, ort, lat, lon, source, to_hide, to_hide_kommentar, datum', 'safe', 'on' => 'search'),
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
			'antraegeOrte' => array(self::HAS_MANY, 'AntragOrt', 'ort_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                => 'ID',
			'ort'               => 'Ort',
			'lat'               => 'Lat',
			'lon'               => 'Lon',
			'source'            => 'Source',
			'to_hide'           => 'To Hide',
			'to_hide_kommentar' => 'To Hide Kommentar',
			'datum'             => 'Datum',
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
		$criteria->compare('ort', $this->ort, true);
		$criteria->compare('lat', $this->lat);
		$criteria->compare('lon', $this->lon);
		$criteria->compare('source', $this->source, true);
		$criteria->compare('to_hide', $this->to_hide);
		$criteria->compare('to_hide_kommentar', $this->to_hide_kommentar, true);
		$criteria->compare('datum', $this->datum, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}

	/**
	 * @param string $name
	 * @throws Exception
	 * @return OrtGeo|null
	 */
	public static function getOrCreate($name)
	{
		/** @var null|OrtGeo */
		$ort = OrtGeo::model()->findByAttributes(array("ort" => $name));
		if ($ort) return $ort;

		$data = RISGeo::addressToGeo("Deutschland", "", "München", $name);
		if (($data === false || $data["lat"] == 0) && mb_strpos($name, "-") !== false) $data = RISGeo::addressToGeo("Deutschland", "", "München", str_replace("-", "", $name));
		if (($data === false || $data["lat"] == 0) && mb_stripos($name, "Str.") !== false) $data = RISGeo::addressToGeo("Deutschland", "", "München", str_ireplace("Str.", "Straße", $name));

		if ($data["lat"] <= 0 || $data["lon"] <= 0) return null;

		$ort = new OrtGeo();
		$ort->ort = $name;
		$ort->lat = $data["lat"];
		$ort->lon = $data["lon"];
		$ort->source = "auto";
		$ort->to_hide = 0;
		$ort->datum = new CDbExpression('NOW()');
		if (!$ort->save()) {
			if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "OrtGeo:getOrCreate Error", print_r($ort->getErrors(), true));
			throw new Exception("Fehler beim Speichern: Geo");
		}
		return $ort;
	}
}