<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "strassen".
 *
 * The followings are the available columns in table 'strassen':
 * @property integer $id
 * @property string $name
 * @property string $plz
 * @property string $osm_ref
 */
class Strasse extends ActiveRecord
{

    /** @var string */
    public $name_normalized;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Strasse the static model class
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
        return 'strassen';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['name', 'required'],
            ['name', 'length', 'max' => 100],
            ['plz', 'length', 'max' => 20],
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
            'name'    => 'Name',
            'plz'     => 'Plz',
            'osm_ref' => 'Osm Ref',
        ];
    }
}