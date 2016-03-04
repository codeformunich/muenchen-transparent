<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "antraege_orte".
 *
 * The followings are the available columns in table 'antraege_orte':
 * @property integer $id
 * @property integer $antrag_id
 * @property integer $termin_id
 * @property integer $rathausumschau_id
 * @property integer $dokument_id
 * @property string $ort_name
 * @property integer $ort_id
 * @property string $source
 * @property string $datum
 *
 * The followings are the available model relations:
 * @property Dokument $dokument
 * @property Antrag $antrag
 * @property Termin $termin
 * @property Rathausumschau $rathausumschau
 * @property OrtGeo $ort
 */
class AntragOrt extends ActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return AntragOrt the static model class
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
        return 'antraege_orte';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['dokument_id, ort_name, ort_id, source, datum', 'required'],
            ['antrag_id, termin_id, rathausumschau_id, dokument_id, ort_id', 'numerical', 'integerOnly' => true],
            ['ort_name', 'length', 'max' => 100],
            ['source', 'length', 'max' => 10],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDokument()
    {
        return $this->hasOne(Dokument::className(), ['id' => 'dokument_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAntrag()
    {
        return $this->hasOne(Antrag::className(), ['id' => 'antrag_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTermin()
    {
        return $this->hasOne(Tagesordnungspunkt::className(), ['id' => 'termin_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRathausumschau()
    {
        return $this->hasOne(Rathausumschau::className(), ['id' => 'rathausumschau_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrt()
    {
        return $this->hasOne(OrtGeo::className(), ['id' => 'ort_id']);
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'antrag_id'         => 'Antrag',
            'termin_id'         => 'Termin',
            'rathausumschau_id' => 'Rathausumschau',
            'dokument_id'       => 'Dokument',
            'ort_name'          => 'Ort Name',
            'ort_id'            => 'Ort',
            'source'            => 'Source',
            'datum'             => 'Datum',
        ];
    }
}