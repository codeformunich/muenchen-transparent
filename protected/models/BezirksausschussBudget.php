<?php

/**
 * @property integer $ba_nr
 * @property integer $jahr
 * @property integer $budget
 * @property integer $vorjahr_rest
 * @property integer $cache_aktuell
 *
 * The followings are the available model relations:
 * @property Bezirksausschuss $bezirksausschuss
 */
class BezirksausschussBudget extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return StadtraetInGremium the static model class
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
        return 'bezirksausschuss_budget';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('ba_nr, jahr, budget, vorjahr_rest', 'required'),
            array('ba_nr, jahr, budget, vorjahr_rest, cache_aktuell', 'numerical', 'integerOnly' => true),
            array('budget, vorjahr_rest, cache_aktuell', 'safe'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'bezirksausschuss' => array(self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'ba_nr'         => 'Bezirksausschuss Nr.',
            'jahr'          => 'Jahr',
            'budget'        => 'Jahresbudget',
            'vorjahr_rest'  => 'Übertrag d. Vorjahr',
            'cache_aktuell' => 'Aktuelles Restguthaben',
        );
    }
}