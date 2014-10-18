<?php

/**
 * This is the model class for table "bezirksausschuesse".
 *
 * The followings are the available columns in table 'bezirksausschuesse':
 * @property integer $ba_nr
 * @property integer $ris_id
 * @property string $name
 * @property string $website
 * @property integer $osm_init_zoom
 * @property string $osm_shape
 *
 * The followings are the available model relations:
 * @property Antrag[] $antraege
 * @property AntragHistory[] $antraegeHistories
 * @property Gremium[] $gremien
 * @property GremiumHistory[] $gremienHistories
 * @property RISAenderung[] $RISAenderungen
 * @property StadtraetIn[] $stadtraetInnen
 */
class Bezirksausschuss extends CActiveRecord
{
	/**
	 * @param string $className active record class name.
	 * @return Bezirksausschuss the static model class
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
		return 'bezirksausschuesse';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ba_nr, ris_id', 'required'),
			array('ba_nr, ris_id, osm_init_zoom', 'numerical', 'integerOnly' => true),
			array('name', 'length', 'max' => 100),
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
			'antraege'          => array(self::HAS_MANY, 'Antrag', 'ba_nr'),
			'antraegeHistories' => array(self::HAS_MANY, 'AntragHistory', 'ba_nr'),
			'gremien'           => array(self::HAS_MANY, 'Gremium', 'ba_nr'),
			'gremienHistories'  => array(self::HAS_MANY, 'GremiumHistory', 'ba_nr'),
			'RISAenderungen'    => array(self::HAS_MANY, 'RisAenderung', 'ba_nr'),
			'stadtraetInnen'    => array(self::HAS_MANY, 'StadtraetIn', 'ba_nr'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'ba_nr'         => 'BA Nr',
			'ris_id'        => "RIS ID",
			'name'          => 'Name',
			'website'       => 'Website',
			'osm_init_zoom' => 'OSM Zoom',
			'osm_shape'     => 'OSM Shape',
		);
	}


	public function toGeoJSONArray()
	{
		return array(
			"type"       => "Feature",
			"id"         => $this->ba_nr,
			"properties" => array(
				"name"    => $this->name,
				"website" => $this->website
			),
			"init_zoom"  => IntVal($this->osm_init_zoom),
			"geometry"   => array(
				"type"        => "Polygon",
				"coordinates" => array(json_decode($this->osm_shape))
			)
		);
	}


	private $kontur_cache = null;

	/**
	 * @param float $point_lon
	 * @param float $point_lat
	 * @return bool
	 */
	public function pointInBA($point_lon, $point_lat)
	{
		// Check if the point is inside the polygon or on the boundary
		if ($this->kontur_cache === null) $this->kontur_cache = json_decode($this->osm_shape);
		$intersections  = 0;
		$vertices_count = count($this->kontur_cache);

		for ($i = 1; $i < $vertices_count; $i++) {
			$vertex1 = $this->kontur_cache[$i - 1];
			$vertex2 = $this->kontur_cache[$i];
			if ($vertex1[1] == $vertex2[1] and $vertex1[1] == $point_lat and $point_lon > min($vertex1[0], $vertex2[0]) and $point_lon < max($vertex1[0], $vertex2[0])) { // Check if point is on an horizontal polygon boundary
				return true;
			}
			if ($point_lat > min($vertex1[1], $vertex2[1]) and $point_lat <= max($vertex1[1], $vertex2[1]) and $point_lon <= max($vertex1[0], $vertex2[0]) and $vertex1[1] != $vertex2[1]) {
				$xinters = ($point_lat - $vertex1[1]) * ($vertex2[0] - $vertex1[0]) / ($vertex2[1] - $vertex1[1]) + $vertex1[0];
				if ($xinters == $point_lon) { // Check if point is on the polygon boundary (other than horizontal)
					return true;
				}
				if ($vertex1[0] == $vertex2[0] || $point_lon <= $xinters) {
					$intersections++;
				}
			}
		}
		// If the number of edges we passed through is odd, then it's in the polygon.
		if ($intersections % 2 != 0) {
			return true;
		} else {
			return false;
		}
	}


}