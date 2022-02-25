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
 * @property string $created
 * @property string $modified
 *
 * The followings are the available model relations:
 * @property Antrag[] $antraege
 * @property AntragHistory[] $antraegeHistories
 * @property Gremium[] $gremien
 * @property GremiumHistory[] $gremienHistories
 * @property RISAenderung[] $RISAenderungen
 * @property StadtraetIn[] $stadtraetInnen
 * @property Fraktion[] $fraktionen
 * @property BezirksausschussBudget[] $budgets
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
        return [
            ['ba_nr, ris_id', 'required'],
            ['ba_nr, ris_id, osm_init_zoom', 'numerical', 'integerOnly' => true],
            ['name', 'length', 'max' => 100],
            ['created, modified', 'safe'],
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
            'antraege'          => [self::HAS_MANY, 'Antrag', 'ba_nr'],
            'antraegeHistories' => [self::HAS_MANY, 'AntragHistory', 'ba_nr'],
            'gremien'           => [self::HAS_MANY, 'Gremium', 'ba_nr'],
            'gremienHistories'  => [self::HAS_MANY, 'GremiumHistory', 'ba_nr'],
            'RISAenderungen'    => [self::HAS_MANY, 'RisAenderung', 'ba_nr'],
            'stadtraetInnen'    => [self::HAS_MANY, 'StadtraetIn', 'ba_nr'],
            'fraktionen'        => [self::HAS_MANY, 'Fraktion', 'ba_nr'],
            'budgets'           => [self::HAS_MANY, 'BezirksausschussBudget', 'ba_nr'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'ba_nr'         => 'BA Nr',
            'ris_id'        => "RIS ID",
            'name'          => 'Name',
            'website'       => 'Website',
            'osm_init_zoom' => 'OSM Zoom',
            'osm_shape'     => 'OSM Shape',
            'budgets'       => 'Budgets',
        ];
    }

    /**
     * @return array Die Fläche des Stadtbezirks als GeoJSON-Feature
     */
    public function toGeoJSONArray()
    {
        return [
            "type"       => "Feature",
            "id"         => $this->ba_nr,
            "properties" => [
                "name"    => $this->name,
                "website" => $this->website
            ],
            "init_zoom"  => IntVal($this->osm_init_zoom),
            "geometry"   => [
                "type"        => "Polygon",
                "coordinates" => [json_decode($this->osm_shape)]
            ]
        ];
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
        if ($this->kontur_cache === null) {
            $this->kontur_cache = json_decode($this->osm_shape);
        }

        $kontur = $this->kontur_cache;
        $first  = $kontur[0];
        $last   = $kontur[count($kontur) - 1];
        if ($first[0] != $last[0] || $first[1] != $last[1]) {
            $kontur[] = $first;
        }

        $intersections  = 0;
        $vertices_count = count($kontur);

        for ($i = 1; $i < $vertices_count; $i++) {
            $vertex1 = $kontur[$i - 1];
            $vertex2 = $kontur[$i];
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

    /**
     * @return StadtraetInGremium[]
     */
    public function mitgliederMitFunktionen()
    {
        $vollgremium = null;
        foreach ($this->gremien as $gremium) {
            if ($gremium->gremientyp == "BA-Vollgremium") {
                $vollgremium = $gremium;
            }
        }
        if (!$vollgremium) {
            return [];
        }
        $funktionen = [];
        $blacklists = ['Fraktions-Mitglied', 'Mitglied', 'BA-Mitglied'];
        foreach ($vollgremium->mitgliedschaften as $mitgliedschaft) {
            if (!in_array($mitgliedschaft->funktion, $blacklists)) {
                $funktionen[] = $mitgliedschaft;
            }
        }

        $funktion2weight = function ($funktion) {
            $funktion = trim($funktion);
            if (in_array($funktion, ["Vorsitz", "BA-Vorsitz", "BA-Vorsitzender", "BA-Vorsitzende", "Vorsitzender", "Vorsitzende", "Sitzungsleitung", "Vorsitzende/r"])) {
                return 1;
            }
            if (mb_stripos($funktion, "1. stell") === 0 || mb_stripos($funktion, "1. stv") === 0 || in_array($funktion, ["stellv. Vorsitz"])) {
                return 2;
            }
            if (mb_stripos($funktion, "2. stell") === 0 || mb_stripos($funktion, "2. stv") === 0) {
                return 3;
            }

            return 10;
        };
        usort($funktionen, function ($funk1, $funk2) use ($funktion2weight) {
            /** @var StadtraetInGremium $funk1 */
            /** @var StadtraetInGremium $funk2 */
            if ($funktion2weight($funk1->funktion) < $funktion2weight($funk2->funktion)) {
                return -1;
            }
            if ($funktion2weight($funk1->funktion) > $funktion2weight($funk2->funktion)) {
                return 1;
            }
            return 0;
        });
        return $funktionen;
    }

    public function getLink(): string
    {
        if ($this->ba_nr != 0)
            return Yii::app()->createUrl("index/ba", ["ba_nr" => $this->ba_nr, "ba_name" => $this->name]);
        else
            return Yii::app()->createUrl("index/startseite");
    }

    // https://stadt.muenchen.de/dam/jcr:cc9cc6c7-cfd6-4d9a-8f33-bed0dbec974a/LHM_Stat.Taschenbuch_2021.pdf
    public const BA_EINWOHNERINNEN = [
        "1"  => 20960,
        "2"  => 51547,
        "3"  => 51530 ,
        "4"  => 68750,
        "5"  => 62353,
        "6"  => 40916,
        "7"  => 60468,
        "8"  => 29328,
        "9"  => 99704,
        "10" => 54934,
        "11" => 75999,
        "12" => 78881,
        "13" => 91855,
        "14" => 46915,
        "15" => 74456,
        "16" => 118147,
        "17" => 53897,
        "18" => 52940,
        "19" => 98596,
        "20" => 49770,
        "21" => 77301,
        "22" => 50140,
        "23" => 33710,
        "24" => 62270,
        "25" => 56729,
    ];

    public function getInteressanteStatistik(): array
    {
        $statistiken = [];
        if (isset(static::BA_EINWOHNERINNEN[$this->ba_nr])) {
            $statistiken[] = [
                "name" => "Einwohner*innen (Oktober 2019)",
                "wert" => static::BA_EINWOHNERINNEN[$this->ba_nr],
            ];
        }

        return $statistiken;

    }

    /**
     * @return static[]
     */
    public function alleOhneStadtrat() {
        return Bezirksausschuss::model()->findAll(["condition" => "ba_nr != 0"]);
    }
}
