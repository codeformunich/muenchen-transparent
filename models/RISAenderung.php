<?php

/**
 * This is the model class for table "ris_aenderungen".
 *
 * The followings are the available columns in table 'ris_aenderungen':
 * @property integer $id
 * @property integer $ris_id
 * @property integer $ba_nr
 * @property string $typ
 * @property string $datum
 * @property string $aenderungen
 *
 * The followings are the available model relations:
 * @property Bezirksausschuss $ba
 */
class RISAenderung extends CActiveRecord
{

    public static $TYP_STADTRAT_ANTRAG = "stadtrat_antrag";
    public static $TYP_STADTRAT_VORLAGE = "stadtrat_vorlage";
    public static $TYP_STADTRAT_TERMIN = "stadtrat_termin";
    public static $TYP_STADTRAT_GREMIUM = "stadtrat_gremium";
    public static $TYP_STADTRAT_FRAKTION = "stadtrat_fraktion";
    public static $TYP_STADTRAT_ERGEBNIS = "stadtrat_ergebnis";
    public static $TYP_STADTRAETIN = "stadtraetIn";
    public static $TYP_BA_ANTRAG = "ba_antrag";
    public static $TYP_BA_INITIATIVE = "ba_initiative";
    public static $TYP_BUERGERVERSAMMLUNG_EMPFEHLUNG = "bv_empfehlung";
    public static $TYP_BA_TERMIN = "ba_termin";
    public static $TYP_BA_GREMIUM = "ba_gremium";
    public static $TYP_RATHAUSUMSCHAU = "rathausumschau";
    public static $TYP_BA_MITGLIED = "ba_mitglied";
    public static $TYP_BA_ERGEBNIS = "ba_ergebnis";
    public static $TYPEN_ALLE = [
        "stadtrat_antrag"   => "stadtratsantrag",
        "stadtrat_vorlage"  => "Stadtratsvorlage",
        "stadtrat_termin"   => "Stadtratstermin",
        "stadtrat_gremium"  => "Stadtratsgremium",
        "stadtrat_ergebnis" => "Stadtratstagesordnung",
        "stadtraetIn"       => "StadträtIn",
        "ba_antrag"         => "BA-Antrag",
        "ba_initiative"     => "BA-Initiative",
        "bv_empfehlung"     => "BV-Empfehlung",
        "ba_termin"         => "BA-Termin",
        "ba_gremium"        => "BA-Gremium",
        "ba_mitglied"       => "BA-Mitglied",
        "ba_ergebnis"       => "BA-Tagesordnung",
        "rathausumschau"    => "Rathausumschau",
    ];

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return RISAenderung the static model class
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
        return 'ris_aenderungen';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['ris_id, typ, datum, aenderungen', 'required'],
            ['ris_id, ba_nr', 'numerical', 'integerOnly' => true],
            ['typ', 'length', 'max' => 20],
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
            'baNr' => [self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'ris_id'      => 'Ris',
            'ba_nr'       => 'Ba Nr',
            'typ'         => 'Typ',
            'datum'       => 'Datum',
            'aenderungen' => 'Aenderungen',
        ];
    }

    /**
     * @return IRISItem|null
     */
    public function getRISItem()
    {
        switch ($this->typ) {
            case RISAenderung::$TYP_STADTRAT_VORLAGE:
            case RISAenderung::$TYP_STADTRAT_ANTRAG:
            case RISAenderung::$TYP_BA_ANTRAG:
            case RISAenderung::$TYP_BA_INITIATIVE:
                return Antrag::model()->findByPk($this->ris_id);
                break;
            case RISAenderung::$TYP_BA_GREMIUM:
            case RISAenderung::$TYP_STADTRAT_GREMIUM:
                return Gremium::model()->findByPk($this->ris_id);
                break;
            case RISAenderung::$TYP_STADTRAT_TERMIN:
            case RISAenderung::$TYP_BA_TERMIN:
                return Termin::model()->findByPk($this->ris_id);
                break;
            case RISAenderung::$TYP_BA_MITGLIED:
            case RISAenderung::$TYP_STADTRAETIN:
                return StadtraetIn::model()->findByPk($this->ris_id);
                break;
            case RISAenderung::$TYP_RATHAUSUMSCHAU:
                return null; // @TODO
                break;
            case RISAenderung::$TYP_STADTRAT_FRAKTION:
                return null; // @TODO
                break;
            default:
                return null;
        }
    }

    /**
     * @return array
     */
    public function toFeedData()
    {
        $item = $this->getRISItem();
        return [
            "title"          => ($item ? $item->getTypName() . ": " . $item->getName() : "?"),
            "link"           => ($item ? $item->getLink() : "-"),
            "content"        => nl2br(CHtml::encode($this->aenderungen)),
            "dateCreated"    => RISTools::date_iso2timestamp($this->datum),
            "aenderung_guid" => Yii::app()->createUrl("aenderung/anzeigen", ["id" => $this->id])
        ];
    }
}