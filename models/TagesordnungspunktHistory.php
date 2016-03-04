<?php

namespace app\models;

use app\components\RISTools;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tagesordnungspunkte_history".
 *
 * The followings are the available columns in table 'tagesordnungspunkte_history':
 * @property integer $id
 * @property integer $vorgang_id
 * @property string $datum_letzte_aenderung
 * @property integer $antrag_id
 * @property string $gremium_name
 * @property integer $gremium_id
 * @property integer $sitzungstermin_id
 * @property string $sitzungstermin_datum
 * @property string $beschluss_text
 * @property string $entscheidung
 * @property string $top_nr
 * @property int $top_ueberschrift
 * @property string $top_betreff
 * @property string $status
 *
 * The followings are the available model relations:
 * @property Termin $sitzungstermin
 * @property Gremium $gremium
 * @property Antrag $antrag
 * @property Dokument[] $dokumente
 */
class TagesordnungspunktHistory extends ActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return TagesordnungspunktHistory the static model class
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
        return 'tagesordnungspunkte_history';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['top_betreff, sitzungstermin_id, sitzungstermin_datum, datum_letzte_aenderung', 'required'],
            ['antrag_id, gremium_id, sitzungstermin_id, top_ueberschrift, vorgang_id', 'numerical', 'integerOnly' => true],
            ['gremium_name', 'length', 'max' => 100],
            ['beschluss_text', 'length', 'max' => 500],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSitzungstermin()
    {
        return $this->hasOne(Termin::className(), ['id' => 'sitzungstermin_id']);
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
    public function getAntrag()
    {
        return $this->hasOne(Antrag::className(), ['id' => 'antrag_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVorgang()
    {
        return $this->hasOne(Vorgang::className(), ['id' => 'vorgang_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDokumente()
    {
        return $this->hasMany(Dokument::className(), ['tagesordnungspunkt_id' => 'id']);
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                     => 'ID',
            'vorgang_id'             => 'Vorgangs-ID',
            'antrag_id'              => 'Antrag',
            'gremium_name'           => 'Gremium Name',
            'gremium_id'             => 'Gremium',
            'sitzungstermin_id'      => 'Sitzungstermin',
            'sitzungstermin_datum'   => 'Sitzungstermin Datum',
            'beschluss_text'         => 'Beschluss',
            'entscheidung'           => 'Entscheidung',
            'datum_letzte_aenderung' => 'Letzte Änderung',
            'top_nr'                 => 'Tagesordnungspunkt',
            'top_ueberschrift'       => 'Ist Überschrift',
            'top_betreff'            => 'Betreff',
            'status'                 => 'Status'
        ];
    }

    /**
     * @param bool $kurzfassung
     * @return string
     */
    public function getName($kurzfassung = false)
    {
        if ($kurzfassung) {
            $betreff = str_replace(["\n", "\r"], [" ", " "], $this->top_betreff);
            $x       = explode(" Antrag Nr.", $betreff);
            return RISTools::korrigiereTitelZeichen($x[0]);
        } else {
            return RISTools::korrigiereTitelZeichen($this->top_betreff);
        }
    }
}
