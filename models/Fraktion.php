<?php

namespace app\models;

use app\components\RISTools;
use app\models\Bezirksausschuss;
use app\models\Person;
use app\models\StadtraetInFraktion;
use yii\db\ActiveRecord;

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
class Fraktion extends ActiveRecord implements IRISItem
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
    public static function tableName()
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBezirksausschuss()
    {
        return $this->hasOne(Bezirksausschuss::className(), ['id' => 'ba_nr']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStadtraetInnenFraktionen()
    {
        return $this->hasMany(StadtraetInFraktion::className(), ['fraktion_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonen()
    {
        return $this->hasMany(Person::className(), ['ris_fraktion' => 'id']);
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

    /**
     * @param array $add_params
     * @return string
     */
    public function getLink($add_params = [])
    {
        if ($this->id < 0) return "#";
        $strs = $this->stadtraetInnenFraktionen;
        return "http://www.ris-muenchen.de/RII/RII/ris_fraktionen_detail.jsp?risid=" . $this->id . "&periodeid=" . $strs[0]->wahlperiode;
    }

    /** @return string */
    public function getTypName()
    {
        return "Fraktion";
    }

    /**
     * @param bool $kurzfassung
     * @return string
     */
    public function getName($kurzfassung = false)
    {
        $name = RISTools::korrigiereTitelZeichen($this->name);
        if ($name == "&nbsp;" || trim($name) == "") return "fraktionslos";
        if ($kurzfassung) {
            if (in_array($this->id, [3339564, 2988265, 3312425])) return "Bürgerliche Mitte";
            if ($this->id == 3312427) return "Freiheitsrechte Transparenz Bürgerbeteiligung";
            if (in_array($this->id, [3312426, 1431959, 33])) return "Die Grünen / RL";
        }
        return $name;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return "0000-00-00 00:00:00";
    }


}