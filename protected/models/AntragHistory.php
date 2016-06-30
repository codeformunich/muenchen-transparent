<?php

/**
 * This is the model class for table "antraege_history".
 *
 * The followings are the available columns in table 'antraege_history':
 * @property integer $id
 * @property integer $vorgang_id
 * @property string $typ
 * @property string $datum_letzte_aenderung
 * @property integer $ba_nr
 * @property string $gestellt_am
 * @property string $gestellt_von
 * @property string $antrags_nr
 * @property string $bearbeitungsfrist
 * @property string $registriert_am
 * @property string $erledigt_am
 * @property string $referat
 * @property string $referent
 * @property int $referat_id
 * @property string $wahlperiode
 * @property string $antrag_typ
 * @property string $betreff
 * @property string $kurzinfo
 * @property string $status
 * @property string $bearbeitung
 * @property string $fristverlaengerung
 * @property string $initiatorInnen
 * @property string $initiative_to_aufgenommen
 *
 * The followings are the available model relations:
 * @property Bezirksausschuss $ba
 */
class AntragHistory extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return AntragHistory the static model class
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
        return 'antraege_history';
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
            ['id, ba_nr, vorgang_id, referat_id', 'numerical', 'integerOnly' => true],
            ['typ', 'length', 'max' => 16],
            ['antrags_nr', 'length', 'max' => 20],
            ['referat', 'length', 'max' => 500],
            ['referent', 'length', 'max' => 200],
            ['wahlperiode, antrag_typ, status', 'length', 'max' => 50],
            ['bearbeitung', 'length', 'max' => 100],
            ['gestellt_am, bearbeitungsfrist, registriert_am, erledigt_am, fristverlaengerung, initiative_to_aufgenommen', 'safe'],
            ['id, typ, datum_letzte_aenderung, ba_nr, gestellt_am, gestellt_von, antrags_nr, bearbeitungsfrist, registriert_am, erledigt_am, referat, referent, wahlperiode, antrag_typ, betreff, kurzinfo, status, bearbeitung, fristverlaengerung, initiatorInnen, initiative_to_aufgenommen, created, modified', 'safe', 'on' => 'insert'],
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
            'ba' => [self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                        => 'ID',
            'vorgang_id'                => 'Vorgangs-ID',
            'typ'                       => 'Typ',
            'datum_letzte_aenderung'    => 'Datum Letzte Aenderung',
            'ba_nr'                     => 'Ba Nr',
            'gestellt_am'               => 'Gestellt Am',
            'gestellt_von'              => 'Gestellt Von',
            'antrags_nr'                => 'Antrags Nr',
            'bearbeitungsfrist'         => 'Bearbeitungsfrist',
            'registriert_am'            => 'Registriert Am',
            'erledigt_am'               => 'Erledigt Am',
            'referat'                   => 'Referat',
            'referent'                  => 'Referent',
            'referat_id'                => 'Referat-ID',
            'wahlperiode'               => 'Wahlperiode',
            'antrag_typ'                => 'Antrag Typ',
            'betreff'                   => 'Betreff',
            'kurzinfo'                  => 'Kurzinfo',
            'status'                    => 'Status',
            'bearbeitung'               => 'Bearbeitung',
            'fristverlaengerung'        => 'Fristverlaengerung',
            'initiatorInnen'            => 'InitiatorInnen',
            'initiative_to_aufgenommen' => 'Initiative To Aufgenommen',
        ];
    }
}
