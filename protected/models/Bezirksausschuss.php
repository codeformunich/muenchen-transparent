<?php

/**
 * This is the model class for table "bezirksausschuesse".
 *
 * The followings are the available columns in table 'bezirksausschuesse':
 * @property integer $ba_nr
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
			array('ba_nr', 'required'),
			array('ba_nr, osm_init_zoom', 'numerical', 'integerOnly' => true),
			array('name', 'length', 'max' => 100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ba_nr, name', 'safe', 'on' => 'search'),
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
			'ba_nr'         => 'Ba Nr',
			'name'          => 'Name',
			'website'       => 'Website',
			'osm_init_zoom' => 'OSM Zoom',
			'osm_shape'     => 'OSM Shape',
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

		$criteria->compare('ba_nr', $this->ba_nr);
		$criteria->compare('name', $this->name, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
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
			"init_zoom"  => IntVal(static::$OSM_ZOOM[$this->ba_nr]),
			"geometry"   => array(
				"type"        => "Polygon",
				"coordinates" => array(json_decode($this->osm_shape))
			)
		);
	}


	public static $NAMEN = array(
		1  => "Altstadt-Lehel",
		2  => "Ludwigsvorstadt-Isarvorstadt",
		3  => "Maxvorstadt",
		4  => "Schwabing-West",
		5  => "Au-Haidhausen",
		6  => "Sendling",
		7  => "Sendling-Westpark",
		8  => "Schwanthalerhöhe",
		9  => "Neuhausen-Nymphenburg",
		10 => "Moosach",
		11 => "Milbertshofen-Am Hart",
		12 => "Schwabing-Freimann",
		13 => "Bogenhausen",
		14 => "Berg am Laim",
		15 => "Trudering-Riem",
		16 => "Ramersdorf-Perlach",
		17 => "Obergiesing",
		18 => "Untergiesing-Harlaching",
		19 => "Thalkirchen-Obersendling-Forstenried-Fürstenried-Solln",
		20 => "Hadern",
		21 => "Pasing-Obermenzing",
		22 => "Aubing-Lochhausen-Langwied",
		23 => "Allach-Untermenzing",
		24 => "Feldmoching-Hasenbergl",
		25 => "Laim",
	);


	public static $HOMEPAGES = array(
		1  => "http://www.muenchen.info/ba/01/",
		2  => "http://www.muenchen.info/ba/02/",
		3  => "http://www.muenchen.info/ba/03/",
		4  => "http://www.muenchen.info/ba/04/ba04_m.htm",
		5  => "http://www.muenchen.info/ba/05/",
		6  => "http://www.muenchen.info/ba/06/",
		7  => "http://www.muenchen.info/ba/07/",
		8  => "http://www.muenchen.info/ba/08/",
		9  => "http://www.muenchen.info/ba/09/",
		10 => "http://www.muenchen.info/ba/10/",
		11 => "http://www.muenchen.info/ba/11/",
		12 => "http://www.muenchen.info/ba/12/",
		13 => "http://www.muenchen.de/Rathaus/politik_ba/239921/BA13.html",
		14 => "http://www.muenchen.info/ba/14/",
		15 => "http://www.muenchen.info/ba/15/",
		16 => "http://www.muenchen.info/ba/16/",
		17 => "http://www.muenchen.info/ba/17/",
		18 => "http://www.ba18.de/",
		19 => "http://www.muenchen.info/ba/19/",
		20 => "http://www.muenchen.info/ba/20/",
		21 => "http://www.muenchen.info/ba/21/ba21_grussworte.php",
		22 => "http://www.muenchen.info/ba/22/",
		23 => "http://www.muenchen.info/ba/23/",
		24 => "http://www.muenchen.info/ba/24/",
		25 => "http://www.muenchen.info/ba/25/",
	);


	public static $OSM_ZOOM = array(
		1  => "13",
		2  => "14",
		3  => "14",
		4  => "14",
		5  => "14",
		6  => "13",
		7  => "13",
		8  => "14",
		9  => "13",
		10 => "13",
		11 => "12",
		12 => "12",
		13 => "13",
		14 => "14",
		15 => "12",
		16 => "13",
		17 => "13",
		18 => "13",
		19 => "13",
		20 => "13",
		21 => "13",
		22 => "12",
		23 => "12",
		24 => "12",
		25 => "14",
	);

	private $kontur_cache = null;

	/**
	 * @param float $point_lon
	 * @param float $point_lat
	 * @return bool
	 */
	public function pointInBA($point_lon, $point_lat) {
		// Check if the point is inside the polygon or on the boundary
		if ($this->kontur_cache === null) $this->kontur_cache = json_decode($this->osm_shape);
		$intersections = 0;
		$vertices_count = count($this->kontur_cache);

		for ($i=1; $i < $vertices_count; $i++) {
			$vertex1 = $this->kontur_cache[$i-1];
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