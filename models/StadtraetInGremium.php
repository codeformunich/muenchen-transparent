<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property integer $stadtraetIn_id
 * @property integer $gremium_id
 * @property string $datum_von
 * @property string $datum_bis
 * @property string $funktion
 *
 * The followings are the available model relations:
 * @property Gremium $gremium
 * @property StadtraetIn $stadtraetIn
 */
class StadtraetInGremium extends ActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return StadtraetInGremium the static model class
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
        return 'stadtraetInnen_gremien';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['stadtraetIn_id, gremium_id, datum_von', 'required'],
            ['stadtraetIn_id, gremium_id', 'numerical', 'integerOnly' => true],
            ['datum_von, datum_bis', 'length', 'max' => 10],
            ['funktion, datum_von, datum_bis', 'safe'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGremium()
    {
        return $this->hasOne(Gremium::className(), ['id' => 'gremium_id']);
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
            'gremium_id'     => 'Gremium',
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