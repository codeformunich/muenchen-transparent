<?php

/**
 * @property int $id
 * @property string $titel
 * @property string $url_base
 * @property string $url_html
 * @property string $url_pdf
 * @property string $str_beschluss
 * @property string $bekanntmachung
 * @property string $html
 * @property string $css
 */
class Rechtsdokument extends CActiveRecord
{
    /**
     * @param string $className active record class name.
     *
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
        return [
            ['id', 'length', 'max' => 200],
            ['titel', 'length', 'max' => 200],
            ['url_base, url_html, url_pdf', 'length', 'max' => 200],
            ['str_beschluss, bekanntmachung', 'length', 'max' => 10],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'id',
            'titel'          => 'titel',
            'url_base'       => 'url-base',
            'url_html'       => 'url-html',
            'url_pdf'        => 'url-pdf',
            'str_beschluss'  => 'Stadtratsbeschluss',
            'bekanntmachung' => 'Bekanntmachung',
            'html'           => 'HTML',
            'css'            => 'CSS',
        ];
    }

    public function alle_sortiert()
    {
        $dokumente = $this->findAll();
        usort($dokumente, function ($dok1, $dok2) {
            /*
             * @var Rechtsdokument $dok1
             * @var Rechtsdokument $dok2
             */
            $name1 = strtolower($dok1->titel_lang());
            $name2 = strtolower($dok2->titel_lang());
            if ($name1 == $name2) {
                return 0;
            }

            return ($name1 > $name2) ? +1 : -1;
        });

        return $dokumente;
    }

    public function titel_lang()
    {
        $titel = $this->titel;
        $titel = preg_replace("/(VO|V) *$/", "verordnung", $titel);
        $titel = preg_replace("/(VO|V) /", "verordnung ", $titel);
        $titel = preg_replace("/O *$/", "ordnung", $titel);
        $titel = preg_replace("/O /", "ordnung ", $titel);
        $titel = preg_replace("/S *$/", "satzung", $titel);
        $titel = preg_replace("/S /", "satzung ", $titel);

        return $titel;
    }
}
