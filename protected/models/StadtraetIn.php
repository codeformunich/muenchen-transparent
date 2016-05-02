<?php

/**
 * @property integer $id
 * @property integer $referentIn
 * @property integer $benutzerIn_id
 * @property string $gewaehlt_am
 * @property string $bio
 * @property string $web
 * @property string $email
 * @property string $name
 * @property string $twitter
 * @property string $facebook
 * @property string $abgeordnetenwatch
 * @property string $geschlecht
 * @property string $kontaktdaten
 * @property string $geburtstag
 * @property string $beruf
 * @property string $beschreibung
 * @property string $quellen
 *
 * The followings are the available model relations:
 * @property Antrag[] $antraege
 * @property Person[] $personen
 * @property BenutzerIn
 * @property StadtraetInFraktion[] $stadtraetInnenFraktionen
 * @property StadtraetInGremium[] $mitgliedschaften
 * @property StadtraetInReferat[] $stadtraetInnenReferate
 */
class StadtraetIn extends CActiveRecord implements IRISItem
{
    public static $GESCHLECHTER = [
        "weiblich"  => "Weiblich",
        "maennlich" => "Männlich",
        "sonstiges" => "Beides passt nicht",
    ];


    /**
     * @return StadtraetIn the static model class
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
        return 'stadtraetInnen';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['id, name, referentIn', 'required'],
            ['id, referentIn, benutzerIn_id', 'numerical', 'integerOnly' => true],
            ['web', 'length', 'max' => 250],
            ['name, email', 'length', 'max' => 100],
            ['twitter', 'length', 'max' => 45],
            ['facebook, abgeordnetenwatch', 'length', 'max' => 200],
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
            'antraege'                 => [self::MANY_MANY, 'Antrag', 'antraege_stadtraetInnen(stadtraetIn_id, antrag_id)', 'order' => 'gestellt_am DESC'],
            'personen'                 => [self::HAS_MANY, 'Person', 'ris_stadtraetIn'],
            'stadtraetInnenFraktionen' => [self::HAS_MANY, 'StadtraetInFraktion', 'stadtraetIn_id', 'order' => 'wahlperiode DESC'],
            'mitgliedschaften'         => [self::HAS_MANY, 'StadtraetInGremium', 'stadtraetIn_id'],
            'stadtraetInnenReferate'   => [self::HAS_MANY, 'StadtraetInReferat', 'stadtraetIn_id'],
            'benutzerIn'               => [self::HAS_ONE, 'BenutzerIn', 'benutzerIn_id'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'benutzerIn_id'     => 'BenutzerIn-ID',
            'gewaehlt_am'       => 'Gewaehlt Am',
            'bio'               => 'Bio',
            'web'               => 'Web',
            'email'             => 'E-Mail-Adresse',
            'name'              => 'Name',
            'twitter'           => 'Twitter',
            'facebook'          => 'Facebook',
            'abgeordnetenwatch' => 'Abgeordnetenwatch',
            'kontaktdaten'      => 'Kontaktdaten',
            'geburtstag'        => 'Geburtstag',
            'beruf'             => 'Beruf',
            'beschreibung'      => 'Beschreibung',
            'quellen'           => 'Quellen',
        ];
    }

    /**
     * @param array $add_params
     * @return string
     */
    public function getLink($add_params = [])
    {
        $name = $this->getName();
        return Yii::app()->createUrl("personen/person", array_merge(["id" => $this->id, "name" => $name], $add_params));
    }


    /** @return string */
    public function getTypName()
    {
        return "Stadtratsmitglied";
    }

    private $titel_erraten    = null;
    private $vorname_erraten  = null;
    private $nachname_erraten = null;

    protected function errateNamen()
    {
        if ($this->vorname_erraten !== null) {
            return;
        }

        preg_match("/^(?<titel>([a-z]+\. )*)(?<name>.*)$/siu", $this->name, $matches);
        if (mb_strpos($this->name, ",") > 0) {
            $x = explode(",", $matches["name"]);
            if (count($x) == 2) {
                $this->vorname_erraten  = trim($x[1]);
                $this->nachname_erraten = trim($x[0]);
                $this->titel_erraten    = trim($matches["titel"]);
            } else {
                $this->vorname_erraten  = $this->name;
                $this->nachname_erraten = $this->titel_erraten = "";
            }
        } else {
            $x = explode(" ", $matches["name"]);
            if (count($x) > 1) {
                $this->nachname_erraten = array_pop($x);
                $this->vorname_erraten  = trim(implode(" ", $x));
                $this->titel_erraten    = trim($matches["titel"]);
            } else {
                $this->vorname_erraten  = $this->name;
                $this->nachname_erraten = $this->titel_erraten = "";
            }
        }
    }

    /**
     * @return string
     */
    public function errateVorname()
    {
        $this->errateNamen();
        return $this->vorname_erraten;
    }

    /**
     * @return string
     */
    public function errateNachname()
    {
        $this->errateNamen();
        return $this->nachname_erraten;
    }

    /**
     * @param bool $kurzfassung
     * @return string
     */
    public function getName($kurzfassung = false)
    {
        if (mb_strpos($this->name, ",") > 0) {
            preg_match("/^(?<titel>([a-z]+\. )*)(?<name>.*)$/siu", $this->name, $matches);
            $titel = trim($matches["titel"]);
            if (strlen($titel) > 0) {
                $titel .= " ";
            }

            $x = explode(",", $matches["name"]);
            if (count($x) == 2) {
                $name = $x[1] . " " . $x[0];
            } else {
                $name = $this->name;
            }
            return $titel . trim($name);
        } else {
            return $this->name;
        }
    }

    /**
     * @param string $name1
     * @param string $name2
     * @return int
     */
    public static function sortByNameCmp($name1, $name2)
    {
        $name1 = preg_replace("/^([a-z]+\. )*/siu", "", $name1);
        $name2 = preg_replace("/^([a-z]+\. )*/siu", "", $name2);
        $name1 = str_replace(["Ä", "Ö", "Ü", "ä", "ö", "ü", "ß"], ["A", "O", "U", "a", "o", "u", "s"], $name1);
        $name2 = str_replace(["Ä", "Ö", "Ü", "ä", "Ö", "ü", "ß"], ["A", "O", "U", "a", "o", "u", "s"], $name2);
        return strnatcasecmp($name1, $name2);
    }

    /**
     * @param StadtraetIn[] $personen
     * @return StadtraetIn[];
     */
    public static function sortByName($personen)
    {
        usort($personen, function ($str1, $str2) {
            /** @var StadtraetIn $str1 */
            /** @var StadtraetIn $str2 */
            return StadtraetIn::sortByNameCmp($str1->getName(), $str2->getName());
        });
        return $personen;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return "0000-00-00 00:00:00";
    }


    /**
     * @return string
     */
    public function getSourceLink()
    {
        $istBAler = false;
        foreach ($this->stadtraetInnenFraktionen as $frakt) if ($frakt->fraktion->ba_nr > 0) $istBAler = true;
        if ($istBAler) {
            return "https://www.ris-muenchen.de/RII/BA-RII/ba_mitglieder_details_mitgliedschaft.jsp?Id=" . $this->id;
        } else {
            return "https://www.ris-muenchen.de/RII/RII/ris_mitglieder_detail.jsp?risid=" . $this->id;
        }
    }

    /**
     *
     */
    private function overrideFraktionsMitgliedschaften()
    {
        if (isset(StadtraetInFraktionOverrides::$FRAKTION_ADD[$this->id])) {
            foreach (StadtraetInFraktionOverrides::$FRAKTION_ADD[$this->id] as $override) {
                $mitgliedschaft                  = new StadtraetInFraktion();
                $mitgliedschaft->stadtraetIn_id  = $this->id;
                $mitgliedschaft->fraktion_id     = $override["fraktion_id"];
                $mitgliedschaft->datum_von       = $override["datum_von"];
                $mitgliedschaft->datum_bis       = $override["datum_bis"];
                $mitgliedschaft->wahlperiode     = $override["wahlperiode"];
                $this->stadtraetInnenFraktionen = array_merge([$mitgliedschaft], $this->stadtraetInnenFraktionen);
            }
        }
        if (isset(StadtraetInFraktionOverrides::$FRAKTION_DEL[$this->id])) {
            $fraktionen_neu = [];
            foreach ($this->stadtraetInnenFraktionen as $mitgliedschaft) {
                $todel = false;
                foreach (StadtraetInFraktionOverrides::$FRAKTION_DEL[$this->id] as $override) {
                    if ($override["datum_von"] == $mitgliedschaft->datum_von && $override["fraktion_id"] == $mitgliedschaft->fraktion_id) {
                        $todel = true;
                    }
                }
                if (!$todel) {
                    $fraktionen_neu[] = $mitgliedschaft;
                }
            }
            $this->stadtraetInnenFraktionen = $fraktionen_neu;
        }
    }

    /**
     * @param string $datum
     * @param int|null $ba_nr
     * @return StadtraetIn[]
     */
    public static function getByFraktion($datum, $ba_nr)
    {
        if ($ba_nr === null) {
            $ba_where = "c.ba_nr IS NULL";
        } else {
            $ba_where = "c.ba_nr = " . IntVal($ba_nr);
        }

        /** @var StadtraetIn[] $strs_in */
        $strs_in = StadtraetIn::model()->findAll([
            'alias' => 'a',
            'order' => 'a.name ASC',
            'with'  => [
                'stadtraetInnenFraktionen'          => [
                    'alias'     => 'b',
                    'condition' => 'b.datum_von <= "' . addslashes($datum) . '" AND (b.datum_bis IS NULL OR b.datum_bis >= "' . addslashes($datum) . '")',
                ],
                'stadtraetInnenFraktionen.fraktion' => [
                    'alias'     => 'c',
                    'condition' => $ba_where,
                ]
            ]]);

        foreach ($strs_in as $key => $strIn) $strIn->overrideFraktionsMitgliedschaften();

        /** @var StadtraetIn[] $strs_out */
        $strs_out = [];
        foreach ($strs_in as $strs) {
            if ($strs->id == 3425214) {
                continue;
            } // Seltsamer ristestuser https://www.ris-muenchen.de/RII/RII/ris_mitglieder_detail_fraktion.jsp?risid=3425214&periodeid=null o_O
            $strs_out[] = $strs;
        }
        return $strs_out;
    }

    /**
     * @param string $datum
     * @param int|null $ba_nr
     * @return array[]
     */
    public static function getGroupedByFraktion($datum, $ba_nr)
    {
        $strs       = static::getByFraktion($datum, $ba_nr);
        $fraktionen = [];
        foreach ($strs as $str) {
            if (count($str->stadtraetInnenFraktionen) == 0) {
                continue;
            }
            if (!isset($fraktionen[$str->stadtraetInnenFraktionen[0]->fraktion_id])) {
                $fraktionen[$str->stadtraetInnenFraktionen[0]->fraktion_id] = [];
            }
            $fraktionen[$str->stadtraetInnenFraktionen[0]->fraktion_id][] = $str;
        }
        return $fraktionen;
    }

    /**
     * @return StadtraetInFraktion[]
     */
    public function getFraktionsMitgliedschaften()
    {
        $this->overrideFraktionsMitgliedschaften();
        return $this->stadtraetInnenFraktionen;
    }
}