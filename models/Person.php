<?php

namespace app\models;

use Yii;
use app\components\RISTools;
use yii\db\ActiveRecord;

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
class Person extends ActiveRecord implements IRISItem
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
    public static function tableName()
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
     * @return \yii\db\ActiveQuery
     */
    public function getStadtraetIn()
    {
        return $this->hasOne(StadtraetIn::className(), ['id' => 'ris_stadtraetIn']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFraktion()
    {
        return $this->hasOne(Fraktion::className(), ['id' => 'ris_fraktion']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAntraegePersonen()
    {
        return $this->hasMany(AntragPerson::className(), ['person_id' => 'id']);
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
     * @param string $name
     * @param string $name_normalized
     * @return Person
     * @throws Exception
     */
    public static function getOrCreate($name, $name_normalized)
    {
        /** @var Person|null $pers */
        $pers = Person::findOne(["name_normalized" => $name_normalized]);
        if (is_null($pers)) {
            $pers                  = new Person();
            $pers->name            = $name;
            $pers->name_normalized = $name_normalized;
            $pers->typ             = static::$TYP_SONSTIGES;
            if (!$pers->save()) {
                RISTools::send_email(Yii::$app->params['adminEmail'], "Person:getOrCreate Error", print_r($pers->getErrors(), true), null, "system");
                throw new Exception("Fehler beim Speichern: Person");
            }
        }
        return $pers;
    }

    /**
     * @param string $datum
     * @return string|null
     */
    public function ratePartei($datum = "")
    {
        if (isset($this->fraktion) && $this->fraktion) return $this->fraktion->getName(true);
        if (!isset($this->stadtraetIn) || is_null($this->stadtraetIn)) return null;
        if (!isset($this->stadtraetIn->stadtraetInnenFraktionen[0]->fraktion)) return null;
        if ($datum != "") foreach ($this->stadtraetIn->stadtraetInnenFraktionen as $fraktionsZ) {
            $dat = str_replace("-", "", $datum);
            if ($dat >= str_replace("-", "", $fraktionsZ->datum_von) && (is_null($fraktionsZ->datum_bis) || $dat <= str_replace("-", "", $fraktionsZ->datum_bis))) return $fraktionsZ->fraktion->getName(true);
        }
        return $this->stadtraetIn->stadtraetInnenFraktionen[0]->fraktion->getName(true);
    }

    /**
     * @param array $add_params
     * @return string
     */
    public function getLink($add_params = [])
    {
        return Yii::$app->createUrl("personen/person", array_merge(["id" => $this->id, "name" => $this->name], $add_params));
    }

    /** @return string */
    public function getTypName()
    {
        return "Stadtratsmitglied";
    }

    /**
     * @param bool $kurzfassung
     * @return string
     */
    public function getName($kurzfassung = false)
    {
        if ($kurzfassung) {
            if (in_array($this->id, [279])) return "Freiheitsrechte Transparenz Bürgerbeteiligung";
        }
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return "0000-00-00 00:00:00";
    }


}