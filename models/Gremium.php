<?php

/**
 * This is the model class for table "gremien".
 *
 * The followings are the available columns in table 'gremien':
 * @property integer $id
 * @property string $datum_letzte_aenderung
 * @property integer $ba_nr
 * @property string $name
 * @property string $kuerzel
 * @property string $gremientyp
 * @property string $referat
 *
 * The followings are the available model relations:
 * @property Tagesordnungspunkt[] $tagesordnungspunkte
 * @property Bezirksausschuss $ba
 * @property Termin[] $termine
 * @property StadtraetInGremium[] $mitgliedschaften
 */
class Gremium extends ActiveRecord implements IRISItem
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Gremium the static model class
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
        return 'gremien';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['id, datum_letzte_aenderung, name, kuerzel, gremientyp', 'required'],
            ['id, ba_nr', 'numerical', 'integerOnly' => true],
            ['name, gremientyp, referat', 'length', 'max' => 100],
            ['kuerzel', 'length', 'max' => 50],
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
            'tagesordnungspunkte' => [self::HAS_MANY, 'Tagesordnungspunkt', 'gremium_id'],
            'ba'                  => [self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'],
            'termine'             => [self::HAS_MANY, 'Termin', 'gremium_id'],
            'mitgliedschaften'    => [self::HAS_MANY, 'StadtraetInGremium', 'gremium_id'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                     => 'ID',
            'datum_letzte_aenderung' => 'Datum Letzte Aenderung',
            'ba_nr'                  => 'Ba Nr',
            'name'                   => 'Name',
            'kuerzel'                => 'Kuerzel',
            'gremientyp'             => 'Gremientyp',
            'referat'                => 'Referat',
        ];
    }

    /**
     * @throws DbException|Exception
     */
    public function copyToHistory()
    {
        $history = new GremiumHistory();
        $history->setAttributes($this->getAttributes(), false);
        try {
            if (!$history->save(false)) {
                RISTools::send_email(Yii::$app->params['adminEmail'], "Gremium:moveToHistory Error", print_r($history->getErrors(), true), null, "system");
                throw new Exception("Fehler");
            }
        } catch (CDbException $e) {
            if (strpos($e->getMessage(), "Duplicate entry") === false) throw $e;
        }

    }


    public static function parse_stadtrat_gremien($ris_id)
    {
        $ris_id = IntVal($ris_id);
        echo "- Gremium $ris_id\n";

        $html_details = RISTools::load_file("http://www.ris-muenchen.de/RII/RII/ris_gremien_detail.jsp?risid=" . $ris_id);

        $daten                         = new Gremium();
        $daten->id                     = $ris_id;
        $daten->datum_letzte_aenderung = new DbExpression('NOW()');
        $daten->ba_nr                  = null;

        if (preg_match("/introheadline\">([^>]+)<\/h3/siU", $html_details, $matches)) $daten->name = $matches[1];
        if (preg_match("/rzel:.*detail_div\">([^>]*)<\//siU", $html_details, $matches)) $daten->kuerzel = $matches[1];
        if (preg_match("/Gremientyp:.*detail_div\">([^>]*)<\//siU", $html_details, $matches)) $daten->gremientyp = $matches[1];
        if (preg_match("/Referat:.*detail_div\">([^>]*)<\//siU", $html_details, $matches)) $daten->referat = $matches[1];

        foreach ($daten as $key => $val) $daten[$key] = ($val === null ? null : html_entity_decode(trim($val), ENT_COMPAT, "UTF-8"));

        $aenderungen = "";

        /** @var Gremium $alter_eintrag */
        $alter_eintrag = Gremium::model()->findByPk($ris_id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($alter_eintrag->name != $daten->name) $aenderungen .= "Name: " . $alter_eintrag->name . " => " . $daten->name . "\n";
            if ($alter_eintrag->kuerzel != $daten->kuerzel) $aenderungen .= "Kürzel: " . $alter_eintrag->kuerzel . " => " . $daten->kuerzel . "\n";
            if ($alter_eintrag->gremientyp != $daten->gremientyp) $aenderungen .= "Gremientyp: " . $alter_eintrag->gremientyp . " => " . $daten->gremientyp . "\n";
            if ($alter_eintrag->referat != $daten->referat) $aenderungen .= "Referat: " . $alter_eintrag->referat . " => " . $daten->referat . "\n";
            if ($aenderungen != "") $changed = true;
        }

        if ($changed) {
            if ($alter_eintrag) {
                $alter_eintrag->copyToHistory();
                $alter_eintrag->setAttributes($daten->getAttributes());
                if (!$alter_eintrag->save()) {
                    echo "Gremium 1";
                    var_dump($alter_eintrag->getErrors());
                    die("Fehler");
                }
                $daten = $alter_eintrag;
            } else {
                if (!$daten->save()) {
                    echo "Gremium 2";
                    var_dump($daten->getErrors());
                    die("Fehler");
                }
            }

            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = null;
            $aend->typ         = RISAenderung::$TYP_STADTRAT_GREMIUM;
            $aend->datum       = new DbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();
        }

    }

    /**
     * @param array $add_params
     * @return string
     */
    public function getLink($add_params = [])
    {
        return Yii::$app->createUrl("gremium/anzeigen", array_merge(["id" => $this->id], $add_params));
    }


    /** @return string */
    public function getTypName()
    {
        if ($this->ba_nr > 0) return "BA-Gremium";
        else return "Stadtratsgremium";
    }

    /**
     * @param bool $kurzfassung
     * @return string
     */
    public function getName($kurzfassung = false)
    {
        $name = $this->name;
        if ($kurzfassung) {
            $name = preg_replace("/UA *[0-9]+/", "", $name);
            $name = preg_replace("/^[0-9]+[ _]/", "", $name);
            $name = preg_replace("/^UA /", "", $name);
            $name = trim($name, " \n\t-");
        }
        return $name;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->datum_letzte_aenderung;
    }


}