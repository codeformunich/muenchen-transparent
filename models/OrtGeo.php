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
 * @property int $ba_nr
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
        return [
            ['ort, lat, lon, source, to_hide, datum', 'required'],
            ['to_hide, ba_nr', 'numerical', 'integerOnly' => true],
            ['lat, lon', 'numerical'],
            ['ort', 'length', 'max' => 100],
            ['source', 'length', 'max' => 6],
            ['to_hide_kommentar', 'length', 'max' => 200],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return [
            'antraegeOrte' => [self::HAS_MANY, 'AntragOrt', 'ort_id'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'ort'               => 'Ort',
            'lat'               => 'Lat',
            'lon'               => 'Lon',
            'source'            => 'Source',
            'to_hide'           => 'To Hide',
            'to_hide_kommentar' => 'To Hide Kommentar',
            'datum'             => 'Datum',
        ];
    }

    /**
     * @param string $name
     * @throws Exception
     * @return OrtGeo|null
     */
    public static function getOrCreate($name)
    {
        /** @var null|OrtGeo */
        $ort = OrtGeo::model()->findByAttributes(["ort" => $name]);
        if ($ort) return $ort;

        $data = RISGeo::addressToGeo("Deutschland", "", "München", $name);
        if (($data === false || $data["lat"] == 0) && mb_strpos($name, "-") !== false) $data = RISGeo::addressToGeo("Deutschland", "", "München", str_replace("-", "", $name));
        if (($data === false || $data["lat"] == 0) && mb_stripos($name, "Str.") !== false) $data = RISGeo::addressToGeo("Deutschland", "", "München", str_ireplace("Str.", "Straße", $name));

        if ($data["lat"] <= 0 || $data["lon"] <= 0) return null;

        $ort      = new OrtGeo();
        $ort->ort = $name;
        $ort->lat = $data["lat"];
        $ort->lon = $data["lon"];
        $ort->setzeBA();
        $ort->source  = "auto";
        $ort->to_hide = 0;
        $ort->to_hide_kommentar = "";
        $ort->datum   = new CDbExpression('NOW()');
        if (!$ort->save()) {
            RISTools::send_email(Yii::app()->params['adminEmail'], "OrtGeo:getOrCreate Error", print_r($ort->getErrors(), true), null, "system");
            throw new Exception("Fehler beim Speichern: Geo");
        }
        return $ort;
    }


    public function setzeBA()
    {
        /** @var Bezirksausschuss[] $bas */
        $bas = Bezirksausschuss::model()->findAll();

        $this->ba_nr = null;
        foreach ($bas as $ba) if ($this->ba_nr === null && $ba->pointInBA($this->lon, $this->lat)) {
            $this->ba_nr = $ba->ba_nr;
        }
    }

    /**
     * @param float $lng
     * @param float $lat
     * @return OrtGeo
     */
    public static function findClosest($lng, $lat)
    {
        // SQRT(POW(69.1 * (fld_lat - ( $lat )), 2) + POW(69.1 * (($lon) - fld_lon) * COS(fld_lat / 57.3 ), 2 )) AS distance
        $lat    = FloatVal($lat);
        $lng    = FloatVal($lng);
        $result = Yii::app()->db->createCommand("SELECT *, SQRT(POW(69.1 * (lat - ( " . $lat . ")), 2) + POW(69.1 * (($lng) - lon) * COS(lat / 57.3 ), 2 )) AS distance FROM orte_geo ORDER BY distance ASC LIMIT 0,1")->queryAll();
        $res    = new OrtGeo();
        $res->setAttributes($result[0]);
        return $res;
    }
}
