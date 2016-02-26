<?php

/**
 * This is the model class for table "termine_history".
 *
 * The followings are the available columns in table 'termine_history':
 *
 * @property int $id
 * @property int $typ
 * @property string $datum_letzte_aenderung
 * @property int $termin_reihe
 * @property int $gremium_id
 * @property int $ba_nr
 * @property string $termin
 * @property int $termin_prev_id
 * @property int $termin_next_id
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
     *
     * @param string $className active record class name.
     *
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
        return [
            ['id, typ, datum_letzte_aenderung', 'required'],
            ['id, typ, termin_reihe, gremium_id, ba_nr, termin_prev_id, termin_next_id', 'numerical', 'integerOnly' => true],
            ['referat, referent, vorsitz', 'length', 'max' => 200],
            ['wahlperiode', 'length', 'max' => 20],
            ['status', 'length', 'max' => 100],
            ['termin', 'safe'],
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
            'id'                     => 'ID',
            'typ'                    => 'Typ',
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
        ];
    }
}
