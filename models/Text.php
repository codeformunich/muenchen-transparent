<?php

/**
 * This is the model class for table "texte".
 *
 * The followings are the available columns in table 'texte':
 * @property integer $id
 * @property integer $typ
 * @property integer $pos
 * @property string $text
 * @property string $titel
 * @property string $edit_datum
 * @property integer $edit_benutzerIn_id
 *
 * The followings are the available model relations:
 * @property BenutzerIn $edit_benutzerIn
 */
class Text extends ActiveRecord
{

    public static $TYP_STD = 0;
    public static $TYP_GLOSSAR = 1;
    public static $TYP_REFERAT = 2;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Text the static model class
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
        return 'texte';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['typ, titel', 'required'],
            ['id, typ, pos, edit_benutzerIn_id', 'numerical', 'integerOnly' => true],
            ['titel', 'length', 'max' => 180],
            ['typ, pos, text, titel', 'safe'],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'edit_benutzerIn' => [self::BELONGS_TO, 'Person', 'edit_benutzerIn_id'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'typ'                => 'Typ',
            'pos'                => 'Position',
            'text'               => 'Text',
            'titel'              => 'Titel',
            'edit_datum'         => 'Zuletzt bearbeitet: Datum',
            'edit_benutzerIn'    => 'Zuletzt bearbeitet: BenutzerIn',
            'edit_benutzerIn_id' => 'Zuletzt bearbeitet: BenutzerIn-ID',
        ];
    }

}
