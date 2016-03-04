<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "antraege_personen".
 *
 * The followings are the available columns in table 'antraege_personen':
 * @property integer $antrag_id
 * @property integer $person_id
 * @property string $typ
 *
 * @property Person $person
 * @property Antrag $antrag
 */
class AntragPerson extends ActiveRecord
{

    public static $TYP_GESTELLT_VON = "gestellt_von";
    public static $TYP_INITIATORIN = "initiator";
    public static $TYPEN_ALLE = [
        "gestellt_von" => "Gestellt von",
        "initiator"    => "InitiatorIn"
    ];


    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return AntragPerson the static model class
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
        return 'antraege_personen';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['antrag_id, person_id', 'required'],
            ['antrag_id, person_id', 'numerical', 'integerOnly' => true],
            ['typ', 'length', 'max' => 12],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPerson()
    {
        return $this->hasOne(Person::className(), ['id' => 'person_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAntrag()
    {
        return $this->hasOne(Antrag::className(), ['id' => 'antrag_id']);
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'antrag_id' => 'Antrag (ID)',
            'person_id' => 'Person (ID)',
            'antrag'    => 'Antrag',
            'person'    => 'Person',
            'typ'       => 'Typ',
        ];
    }
}