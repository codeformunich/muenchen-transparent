<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @propeerty integer $id
 * @property integer $stadtraetIn_id
 * @property integer $referat_id
 * @property string $datum_von
 * @property string $datum_bis
 *
 * The followings are the available model relations:
 * @property Referat $referat
 * @property StadtraetIn $stadtraetIn
 */
class StadtraetInReferat extends ActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return StadtraetInReferat the static model class
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
        return 'stadtraetInnen_referate';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['stadtraetIn_id, referat_id', 'required'],
            ['stadtraetIn_id, referat_id, id', 'numerical', 'integerOnly' => true],
            ['datum_von, datum_bis', 'length', 'max' => 10],
            ['datum_von, datum_bis', 'safe'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferat()
    {
        return $this->hasOne(Referat::className(), ['id' => 'referat_id']);
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
            'id'             => 'id',
            'stadtraetIn_id' => 'StadträtIn',
            'referat_id'     => 'Referat',
            'datum_von'      => 'Von',
            'datum_bis'      => 'Bis',
        ];
    }
}