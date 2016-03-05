<?php

/**
 * This is the model class for table "antraege_orte".
 *
 * The followings are the available columns in table 'antraege_orte':
 *
 * @property int $id
 * @property int $antrag_id
 * @property int $termin_id
 * @property int $rathausumschau_id
 * @property int $dokument_id
 * @property string $ort_name
 * @property int $ort_id
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
class AntragOrt extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return AntragOrt the static model class
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
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return [
            'dokument'       => [self::BELONGS_TO, 'Dokument', 'dokument_id'],
            'antrag'         => [self::BELONGS_TO, 'Antrag', 'antrag_id'],
            'termin'         => [self::BELONGS_TO, 'Tagesordnungspunkt', 'termin_id'],
            'rathausumschau' => [self::BELONGS_TO, 'Rathausumschau', 'rathausumschau_id'],
            'ort'            => [self::BELONGS_TO, 'OrtGeo', 'ort_id'],
        ];
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
