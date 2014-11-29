<?php

/**
 * This is the model class for table "texte".
 *
 * The followings are the available columns in table 'texte':
 * @property integer $id
 * @property string $name
 * @property string $url_base
 * @property string $url_pdf
 * @property string $str_beschluss
 * @property string $bekanntmachung
 * @property string $nr
 * @property string $html
 * @property string $css
 *
 */
class Rechtsdokument extends CActiveRecord
{

    /**
     * @param string $className active record class name.
     * @return Rechtsdokument the static model class
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
        return 'rechtsdokument';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('url_base, name, nr', 'required'),
            array('id', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 200),
            array('url_base, url_base', 'length', 'max' => 100),
            array('str_beschluss, bekanntmachung', 'length', 'max' => 10),
            array('nr', 'length', 'max' => 45),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'             => 'ID',
            'name'           => 'Name',
            'url_base'       => 'URL-Base',
            'url_pdf'        => 'PDF-URL',
            'str_beschluss'  => 'Stadtratsbeschluss',
            'bekanntmachung' => 'Bekanntmachung',
            'nr'             => 'Dokumentennummer',
            'html'           => 'HTML',
            'css'            => 'CSS'
        );
    }

}