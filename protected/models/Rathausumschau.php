<?php

/**
 * @property integer $id
 * @property string $datum
 * @property string $url
 * @property integer $jahr
 * @property integer $nr
 *
 * @property Dokument[] $dokumente
 */
class Rathausumschau extends CActiveRecord implements IRISItem
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
        return 'rathausumschau';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['url, datum, jahr, nr', 'required'],
            ['id, jahr, nr', 'numerical', 'integerOnly' => true],
            ['url', 'length', 'max' => 200],
            ['datum', 'length', 'max' => 10],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'dokumente' => [self::HAS_MANY, 'Dokument', 'rathausumschau_id'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'    => 'ID',
            'datum' => 'Datum',
            'url'   => 'URL',
            'jahr'  => 'Jahr',
            'nr'    => 'Nr.',
        ];
    }

    /**
     * @param array $add_params
     * @return string
     */
    public function getLink($add_params = [])
    {
        if (count($this->dokumente) > 0) {
            return $this->dokumente[0]->getLink();
        } else {
            return RATHAUSUMSCHAU_WEBSITE;
        }
    }

    /** @return string */
    public function getTypName()
    {
        return "Rathausumschau";
    }

    /** @return string */
    public function getDate()
    {
        return $this->datum;
    }

    /**
     * @param bool $kurzfassung
     * @return string
     */
    public function getName($kurzfassung = false)
    {
        if ($kurzfassung) return "Rathausumschau " . $this->nr . "/" . substr($this->datum, 0, 4);
        else return "Rathausumschau " . $this->nr . " (" . $this->datum . ")";
    }

    /**
     * @return string[]
     */
    public function inhaltsverzeichnis()
    {
        if (count($this->dokumente) == 0) return [];
        $dok = $this->dokumente[0]->text_pdf;
        $x   = explode("Inhaltsverzeichnis", $dok);
        if (count($x) == 1) return [];

        $x        = explode("Antworten auf Stadtratsanfragen", $x[1]);
        $text     = $x[0];
        $tops_out = [];
        $link     = $this->dokumente[0]->getLinkZumDokument();

        $tops_in = explode("›", $text);
        if (count($tops_in) <= 1) return $tops_out;
        for ($i = 1; $i < count($tops_in); $i++) {
            $top = trim(str_replace("\n", " ", $tops_in[$i]));
            preg_match("/^(?<titel>.*)(?<seite> [0-9]+)$/siu", $top, $matches);
            if (isset($matches["seite"])) $tops_out[] = ["titel" => $matches["titel"], "seite" => IntVal($matches["seite"]), "link" => $link . "#page=" . IntVal($matches["seite"])];
            elseif (isset($matches["titel"])) $tops_out[] = ["titel" => $matches["titel"], "seite" => null, "link" => null];
        }
        return $tops_out;
    }
}