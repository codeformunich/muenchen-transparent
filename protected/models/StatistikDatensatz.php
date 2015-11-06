<?php

/**
 * @property integer $id
 * @property integer $quelle
 * @property string $indikator_gruppe
 * @property string $indikator_bezeichnung
 * @property string $indikator_auspraegung
 * @property float $indikator_wert
 * @property float $basiswert_1
 * @property integer $basiswert_1_name
 * @property integer $basiswert_2
 * @property string $basiswert_2_name
 * @property integer $basiswert_3
 * @property string $basiswert_3_name
 * @property integer $basiswert_4
 * @property string $basiswert_4_name
 * @property integer $basiswert_5
 * @property string $basiswert_5_name
 * @property integer $jahr
 * @property string $gliederung
 * @property integer $gliederung_nummer
 * @property string $gliederung_name
 */
class StatistikDatensatz extends CActiveRecord
{
    const QUELLE_BEVOELKERUNG = 1;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return StatistikDatensatz the static model class
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
        return 'statistik_datensaetze';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['quelle', 'required'],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return [];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'      => 'ID',
        ];
    }
}