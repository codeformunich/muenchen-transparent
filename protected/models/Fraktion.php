<?php

/**
 * This is the model class for table "fraktionen".
 *
 * The followings are the available columns in table 'fraktionen':
 * @property integer $id
 * @property string $name
 * @property integer $ba_nr
 * @property string $website
 *
 * The followings are the available model relations:
 * @property StadtraetInFraktion[] $stadtraetInnenFraktionen
 * @property Person[] $personen
 * @property Bezirksausschuss $bezirksausschuss
 */
class Fraktion extends CActiveRecord implements IRISItem
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Fraktion the static model class
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
        return 'fraktionen';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['id, name', 'required'],
            ['id, ba_nr', 'numerical', 'integerOnly' => true],
            ['name', 'length', 'max' => 70],
            ['website', 'length', 'max' => 250],
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
            'stadtraetInnenFraktionen' => [self::HAS_MANY, 'StadtraetInFraktion', 'fraktion_id'],
            'bezirksausschuss'         => [self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'],
            'personen'                 => [self::HAS_MANY, 'Person', 'ris_fraktion'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'    => 'ID',
            'ba_nr' => "BA-Nr",
            'name'  => 'Name',
        ];
    }

    public function getLink(array $add_params = []): string
    {
        if ($this->id < 0) return "#";
        $strs = $this->stadtraetInnenFraktionen;
        return RIS_BASE_URL . "ris_fraktionen_detail.jsp?risid=" . $this->id . "&periodeid=" . $strs[0]->wahlperiode;
    }

    public function getTypName(): string
    {
        return "Fraktion";
    }

    public function getName(bool $kurzfassung = false): string
    {
        $name = RISTools::normalizeTitle($this->name);
        if ($name == "&nbsp;" || trim($name) == "" || trim($name) === 'Parteifrei') return "keine Angabe";
        if ($kurzfassung) {
            if (in_array($this->id, [3339564, 2988265, 3312425])) return "Bürgerliche Mitte";
            if ($this->id == 5987061) return "FDP / Bayernpartei";
            if ($this->id == 5987005) return "LINKE / PARTEI";
            if ($this->id == 3312427) return "FDP - HUT";
            if ($this->id == 4203027) return "Bayernpartei";
            if ($this->id == 5987095) return "ÖDP / FW";
            if ($this->id == 5986278) return "SPD / Volt";
            if (in_array($this->id, [3312426, 1431959, 33])) return "Die Grünen / RL";
        }
        return $name;
    }

    public function getDate(): string
    {
        return "0000-00-00 00:00:00";
    }

    public function getBaNr()
    {
        return $this->ba_nr == null ? 0 : $this->ba_nr;
    }
}
