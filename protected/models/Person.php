<?php

/**
 * This is the model class for table "personen".
 *
 * The followings are the available columns in table 'personen':
 * @property integer $id
 * @property string $name_normalized
 * @property string $typ
 * @property string $name
 * @property integer $ris_stadtraetIn
 * @property integer $ris_fraktion
 *
 * The followings are the available model relations:
 * @property AntragPerson[] $antraegePersonen
 * @property StadtraetIn $stadtraetIn
 * @property Fraktion $fraktion
 */
class Person extends CActiveRecord implements IRISItem
{

    public static $TYP_SONSTIGES = "sonstiges";
    public static $TYP_PERSON = "person";
    public static $TYP_FRAKTION = "fraktion";
    public static $TYPEN_ALLE = [
        "sonstiges" => "Sonstiges / Unbekannt",
        "person"    => "Person",
        "fraktion"  => "Fraktion"
    ];

    /**
     * @param string $className active record class name.
     * @return Person the static model class
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
        return 'personen';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['name_normalized, typ, name', 'required'],
            ['ris_stadtraetIn, ris_fraktion', 'numerical', 'integerOnly' => true],
            ['typ', 'length', 'max' => 9],
            ['name, name_normalized', 'length', 'max' => 100],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'antraegePersonen' => [self::HAS_MANY, 'AntragPerson', 'person_id'],
            'stadtraetIn'      => [self::BELONGS_TO, 'StadtraetIn', 'ris_stadtraetIn'],
            'fraktion'         => [self::BELONGS_TO, 'Fraktion', 'ris_fraktion'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'name_normalized' => 'Name Normalized',
            'typ'             => 'Typ',
            'name'            => 'Name',
            'ris_stadtraetIn' => 'StadträtInnen-ID',
            'ris_fraktion'    => 'Fraktion',
        ];
    }

    /**
     * @throws Exception
     */
    public static function getOrCreate(string $name, string $name_normalized): Person
    {
        /** @var Person|null $pers */
        $pers = Person::model()->findByAttributes(["name_normalized" => $name_normalized]);
        if (is_null($pers)) {
            $pers                  = new Person();
            $pers->name            = $name;
            $pers->name_normalized = $name_normalized;
            $pers->typ             = static::$TYP_SONSTIGES;
            if (!$pers->save()) {
                RISTools::report_ris_parser_error("Person:getOrCreate Error", print_r($pers->getErrors(), true));
                throw new Exception("Fehler beim Speichern: Person");
            }
        }
        return $pers;
    }

    public function ratePartei(?string $datum = ""): ?Gremium
    {
        if (!isset($this->stadtraetIn) || is_null($this->stadtraetIn)) return null;
        $memberships = array_merge(
            $this->stadtraetIn->getMembershipsByType(Gremium::TYPE_STR_FRAKTION),
            $this->stadtraetIn->getMembershipsByType(Gremium::TYPE_BA_FRAKTION),
        );
        if ($datum != "") foreach ($memberships as $fraktionsZ) {
            $dat = str_replace("-", "", $datum);
            if ($dat >= str_replace("-", "", $fraktionsZ->datum_von) && (is_null($fraktionsZ->datum_bis) || $dat <= str_replace("-", "", $fraktionsZ->datum_bis))) {
                return $fraktionsZ->gremium->getName(true);
            }
        }
        if (count($memberships) > 0) {
            return $memberships[0]->gremium;
        } else {
            return null;
        }
    }

    public function rateParteiName(?string $datum = ""): ?string
    {
        return $this->ratePartei($datum)?->getName(true);
    }

    public function getLink(array $add_params = []): string
    {
        return Yii::app()->createUrl("personen/person", array_merge(["id" => $this->id, "name" => $this->name], $add_params));
    }

    public function getTypName(): string
    {
        return "Stadtratsmitglied";
    }

    public function getName(bool $kurzfassung = false): string
    {
        if ($kurzfassung) {
            if (in_array($this->id, [279])) return "Freiheitsrechte Transparenz Bürgerbeteiligung";
        }
        return $this->name;
    }

    public function getDate(): string
    {
        return "0000-00-00 00:00:00";
    }
}
