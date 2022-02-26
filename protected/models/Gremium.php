<?php

/**
 * This is the model class for table "gremien".
 *
 * The followings are the available columns in table 'gremien':
 * @property int $id
 * @property string $datum_letzte_aenderung
 * @property int|null $ba_nr
 * @property string $name
 * @property string $kuerzel
 * @property string $gremientyp
 * @property string $referat
 *
 * The followings are the available model relations:
 * @property Tagesordnungspunkt[] $tagesordnungspunkte
 * @property Bezirksausschuss $ba
 * @property Termin[] $termine
 * @property StadtraetInGremium[] $mitgliedschaften
 */
class Gremium extends CActiveRecord implements IRISItem
{
    const TYPE_STR_OTHER = '-';
    const TYPE_STR_FRAKTION = 'Fraktion';
    const TYPE_STR_AUSSCHUSS = 'Ausschuss';
    const TYPE_BA_UNTERAUSSCHUSS = 'BA-Unterausschuss';
    const TYPE_BA = 'Bezirksausschuss';
    const TYPE_BA_FRAKTION = 'BA-Fraktion';

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Gremium the static model class
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
        return 'gremien';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['id, datum_letzte_aenderung, name, gremientyp', 'required'],
            ['id, ba_nr', 'numerical', 'integerOnly' => true],
            ['name, gremientyp, referat', 'length', 'max' => 100],
            ['kuerzel', 'length', 'max' => 50],
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
            'tagesordnungspunkte' => [self::HAS_MANY, 'Tagesordnungspunkt', 'gremium_id'],
            'ba'                  => [self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'],
            'termine'             => [self::HAS_MANY, 'Termin', 'gremium_id'],
            'mitgliedschaften'    => [self::HAS_MANY, 'StadtraetInGremium', 'gremium_id'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                     => 'ID',
            'datum_letzte_aenderung' => 'Datum Letzte Aenderung',
            'ba_nr'                  => 'Ba Nr',
            'name'                   => 'Name',
            'kuerzel'                => 'Kuerzel',
            'gremientyp'             => 'Gremientyp',
            'referat'                => 'Referat',
        ];
    }

    public static function getOrCreate(int $id, string $name, string $typ, ?int $baNr): Gremium
    {
        $gremium = Gremium::model()->findByPk($id);
        if (!$gremium) {
            echo "Lege Gremium an: " . $name . " ($id)\n";
            $gremium = new Gremium();
            $gremium->id = $id;
            $gremium->name = $name;
            $gremium->gremientyp = $typ;
            $gremium->ba_nr = $baNr;
            $gremium->referat = '';
            $gremium->kuerzel = '';
            $gremium->datum_letzte_aenderung = date("Y-m-d H:i:s");
            if (!$gremium->save()) {
                var_dump($gremium->getErrors());
            }
        }

        return $gremium;
    }

    /**
     * @throws CDbException|Exception
     */
    public function copyToHistory()
    {
        $history = new GremiumHistory();
        $history->setAttributes($this->getAttributes(), false);
        try {
            if (!$history->save(false)) {
                RISTools::report_ris_parser_error("Gremium:moveToHistory Error", print_r($history->getErrors(), true));
                throw new Exception("Fehler");
            }
        } catch (CDbException $e) {
            if (!str_contains($e->getMessage(), "Duplicate entry")) throw $e;
        }

    }

    public function getLink(array $add_params = []): string
    {
        //return Yii::app()->createUrl("gremium/anzeigen", array_merge(["id" => $this->id], $add_params));
        return '#';
    }


    public function getTypName(): string
    {
        if ($this->ba_nr > 0) return "BA-Gremium";
        else return "Stadtratsgremium";
    }

    public function getName(bool $kurzfassung = false): string
    {
        $name = $this->name;
        if ($kurzfassung) {
            switch ($this->name) {
                case 'Bündnis90/Die Grünen/RL-Fraktion';
                case 'DIE GRÜNEN/RL-Fraktion':
                case 'Fraktion Die Grünen - Rosa Liste':
                    return 'Grüne/RL';
                case 'Fraktion ÖDP/München-Liste':
                    return 'ÖDP/Münchner';
                case 'Stadtratsfraktion DIE LINKE. / Die PARTEI';
                    return 'Linke/Partei';
                case 'FDP BAYERNPARTEI Stadtratsfraktion';
                    return 'FDP/Bayernpartei';
                case 'SPD / Volt - Fraktion';
                    return 'SPD/Volt';
            }
            $name = str_replace('-Fraktion', '', $name);
            $name = preg_replace("/UA *[0-9]+/", "", $name);
            $name = preg_replace("/^[0-9]+[ _]/", "", $name);
            $name = preg_replace("/^UA /", "", $name);
            $name = trim($name, " \n\t-");
        }
        return $name;
    }

    public function getDate(): string
    {
        return $this->datum_letzte_aenderung;
    }

    public function getBaNr(): int
    {
        return $this->ba_nr === null ? 0 : intval($this->ba_nr);
    }
}
