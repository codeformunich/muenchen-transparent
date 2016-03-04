<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property integer $stadtraetIn_id
 * @property integer $fraktion_id
 * @property string $datum_von
 * @property string $datum_bis
 * @property int $wahlperiode
 * @property string $mitgliedschaft
 * @property string $funktion
 *
 * The followings are the available model relations:
 * @property Fraktion $fraktion
 * @property StadtraetIn $stadtraetIn
 */
class StadtraetInFraktion extends ActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return StadtraetInFraktion the static model class
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
        return 'stadtraetInnen_fraktionen';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['stadtraetIn_id, fraktion_id, wahlperiode, mitgliedschaft, datum_von', 'required'],
            ['stadtraetIn_id, fraktion_id', 'numerical', 'integerOnly' => true],
            ['wahlperiode', 'length', 'max' => 30],
            ['datum_von, datum_bis', 'length', 'max' => 10],
            ['funktion', 'safe'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFraktion()
    {
        return $this->hasOne(Fraktion::className(), ['id' => 'fraktion_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStadtraetIn()
    {
        return $this->hasOne(StadtraetIn::className(), ['id' => 'stadtraetIn_id']);
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'stadtraetIn_id' => 'StadträtIn',
            'fraktion_id'    => 'Fraktion',
            'wahlperiode'    => 'Wahlperiode',
            'mitgliedschaft' => 'Mitgliedschaft',
            'funktion'       => 'Funktion',
            'datum_von'      => 'Von',
            'datum_bis'      => 'Bis',
        ];
    }

    /**
     * @param string $datum
     * @return bool
     */
    public function mitgliedschaftAktiv($datum = "") {
        if ($datum == "") $datum = date("Y-m-d");
        $datum = str_replace("-", "", $datum);

        if (is_null($this->datum_bis)) return true;
        $bis = str_replace("-", "", $this->datum_bis);

        return ($bis >= $datum);
    }
}