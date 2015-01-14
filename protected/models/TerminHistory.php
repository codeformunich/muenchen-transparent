<?php

/**
 * This is the model class for table "termine_history".
 *
 * The followings are the available columns in table 'termine_history':
 * @property integer $id
 * @property string $datum_letzte_aenderung
 * @property integer $termin_reihe
 * @property integer $gremium_id
 * @property integer $ba_nr
 * @property string $termin
 * @property integer $termin_prev_id
 * @property integer $termin_next_id
 * @property string $sitzungsort
 * @property string $referat
 * @property string $referent
 * @property string $vorsitz
 * @property string $wahlperiode
 * @property string $status
 */
class TerminHistory extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return TerminHistory the static model class
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
        return 'termine_history';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('id, datum_letzte_aenderung', 'required'),
            array('id, termin_reihe, gremium_id, ba_nr, termin_prev_id, termin_next_id', 'numerical', 'integerOnly' => true),
            array('referat, referent, vorsitz', 'length', 'max' => 200),
            array('wahlperiode', 'length', 'max' => 20),
            array('status', 'length', 'max' => 100),
            array('termin', 'safe'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'                     => 'ID',
            'datum_letzte_aenderung' => 'Datum Letzte Aenderung',
            'termin_reihe'           => 'Termin Reihe',
            'gremium_id'             => 'Gremium',
            'ba_nr'                  => 'Ba Nr',
            'termin'                 => 'Termin',
            'termin_prev_id'         => 'Termin Prev',
            'termin_next_id'         => 'Termin Next',
            'sitzungsort'            => 'Sitzungsort',
            'referat'                => 'Referat',
            'referent'               => 'Referent',
            'vorsitz'                => 'Vorsitz',
            'wahlperiode'            => 'Wahlperiode',
            'status'                 => 'Status',
        );
    }
}