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
 * @property string|null $geburtstag
 * @property string $beruf
 * @property string $beschreibung
 * @property string $quellen
 * @property string $created
 * @property string $modified
 *
 * The followings are the available model relations:
 * @property Antrag[] $antraege
 * @property Person[] $personen
 * @property BenutzerIn
 * @property StadtraetInGremium[] $mitgliedschaften
 * @property StadtraetInReferat[] $stadtraetInnenReferate
 */
class StadtraetIn extends CActiveRecord implements IRISItem
{
    public const GESCHLECHTER = [
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
            'antraege'                 => [self::MANY_MANY, 'Antrag', 'antraege_stadtraetInnen(stadtraetIn_id, antrag_id)', 'order' => 'gestellt_am DESC'],
            'personen'                 => [self::HAS_MANY, 'Person', 'ris_stadtraetIn'],
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

    public function getLink(array $add_params = []): string
    {
        return Yii::app()->createUrl("personen/person", array_merge(["id" => $this->id, "name" => $this->getName()], $add_params));
    }


    public function getTypName(): string
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

    public function errateVorname(): string
    {
        $this->errateNamen();
        return $this->vorname_erraten;
    }

    public function errateNachname(): string
    {
        $this->errateNamen();
        return $this->nachname_erraten;
    }

    public function getName(bool $kurzfassung = false): string
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

    public function getDate(): string
    {
        return "0000-00-00 00:00:00";
    }


    public function getSourceLink(): string
    {
        return RIS_URL_PREFIX . "person/detail/" . $this->id . '?tab=mitgliedschaften';
    }

    /**
     * @return StadtraetIn[]
     */
    public static function getByFraktion(?int $ba_nr): array
    {
        if ($ba_nr === null) {
            $ba_where = "c.ba_nr IS NULL";
        } else {
            $ba_where = "c.ba_nr = " . IntVal($ba_nr);
        }

        $today = date('Y-m-d');
        /** @var StadtraetIn[] $strs_in */
        $strs_in = StadtraetIn::model()->findAll([
            'alias' => 'a',
            'order' => 'a.name ASC',
            'with'  => [
                'mitgliedschaften'          => [
                    'alias'     => 'b',
                    'condition' => 'b.datum_von <= "' . addslashes($today) . '" AND (b.datum_bis IS NULL OR b.datum_bis >= "' . addslashes($today) . '")',
                ],
                'mitgliedschaften.gremium' => [
                    'alias'     => 'c',
                    'condition' => $ba_where,
                ]
            ]
        ]);

        /** @var StadtraetIn[] $strs_out */
        $strs_out = [];
        foreach ($strs_in as $strs) {
            $strs_out[] = $strs;
        }
        return $strs_out;
    }

    public static function getGroupedByFraktion(?int $ba_nr): array
    {
        $strs       = static::getByFraktion($ba_nr);
        $fraktionen = [];
        foreach ($strs as $str) {
            $type = ($ba_nr > 0 ? Gremium::TYPE_BA_FRAKTION : Gremium::TYPE_STR_FRAKTION);
            $fraktion = $str->getCurrentMembershipOfType($type);
            $id = $fraktion ? $fraktion->gremium_id : 0;
            if (!isset($fraktionen[$id])) {
                $fraktionen[$id] = [
                    'persons' => [],
                    'name' => $fraktion ? $fraktion->gremium->getName() : 'Fraktionslos',
                    'link' => $fraktion ? $fraktion->gremium->getLink() : '',
                ];
            }
            $fraktionen[$id]['persons'][] = $str;
        }
        return $fraktionen;
    }

    /**
     * @return StadtraetInGremium[]
     */
    public function getMembershipsByType(string $type): array
    {
        $arr = [];
        foreach ($this->mitgliedschaften as $mitgliedschaft) {
            if ($mitgliedschaft->gremium->gremientyp === $type) {
                $arr[] = $mitgliedschaft;
            }
        }

        usort($arr, function (StadtraetInGremium $mitgliedschaft1, StadtraetInGremium $mitgliedschaft2): int {
            $from1 = $mitgliedschaft1->getDatumVonTimestamp();
            $from2 = $mitgliedschaft2->getDatumVonTimestamp();
            return $from2 <=> $from1;
        });

        return $arr;
    }

    public function getCurrentMembershipOfType(string $type): ?StadtraetInGremium
    {
        $memberships = $this->getMembershipsByType($type);
        $currentMemberships = array_filter($memberships, function (StadtraetInGremium $membership): bool {
            return $membership->mitgliedschaftAktiv();
        });

        return count($currentMemberships) > 0 ? $currentMemberships[0] : null;
    }

    public function getAllSearchNames(): array {
        $names = [$this->getName()];
        if (isset(StadtraetInOverrides::$PERSON_ADDITIONAL_NAMES[$this->id])) {
            $names = array_merge($names, StadtraetInOverrides::$PERSON_ADDITIONAL_NAMES[$this->id]);
        }
        return $names;
    }

    public function getDocumentMentions(): \Solarium\QueryType\Select\Result\Result
    {
        $solr   = RISSolrHelper::getSolrClient();
        $select = $solr->createSelect();

        $dismax = $select->getDisMax();
        $dismax->setQueryParser('edismax');
        $dismax->setQueryFields("text text_ocr");

        $names = array_map(function($name) {
            return "\"" . $name . "\"";
        }, $this->getAllSearchNames());
        $select->setQuery(implode(" OR ", $names));

        $select->setRows(50);
        $select->addSort('sort_datum', $select::SORT_DESC);

        $hl = $select->getHighlighting();
        $hl->setFields(['text', 'text_ocr', 'antrag_betreff']);
        $hl->setSimplePrefix('<b>');
        $hl->setSimplePostfix('</b>');

        return $solr->select($select);
    }
}
