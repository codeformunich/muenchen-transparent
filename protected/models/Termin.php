<?php

/**
 * This is the model class for table "termine".
 *
 * The followings are the available columns in table 'termine':
 * @property integer $id
 * @property integer $typ
 * @property string $datum_letzte_aenderung
 * @property integer $termin_reihe
 * @property integer $gremium_id
 * @property integer $ba_nr
 * @property string $termin
 * @property integer $termin_prev_id
 * @property integer $termin_next_id
 * @property string $sitzungsort
 * @property string $referat
 * @property string $referent
 * @property string $vorsitz
 * @property string $wahlperiode
 * @property string $status
 * @property string $sitzungsstand
 *
 * The followings are the available model relations:
 * @property Dokument[] $antraegeDokumente
 * @property Tagesordnungspunkt[] $tagesordnungspunkte
 * @property AntragOrt[] $antraegeOrte
 * @property Gremium $gremium
 * @property Bezirksausschuss $ba
 */
class Termin extends CActiveRecord implements IRISItemHasDocuments
{
    public const CANCELED_STR = 'Entfällt';

    public const TYP_AUTO = 0;
    public const TYP_BUERGERVERSAMMLUNG = 1;
    public const TYPEN_ALLE = [
        0 => "Automatisch vom RIS",
        1 => "BürgerInnenversammlung",
    ];


    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Termin the static model class
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
        return 'termine';
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
            ['id, typ, termin_reihe, gremium_id, ba_nr, termin_prev_id, termin_next_id', 'numerical', 'integerOnly' => true],
            ['referat, referent, vorsitz', 'length', 'max' => 200],
            ['wahlperiode', 'length', 'max' => 20],
            ['status, sitzungsstand', 'length', 'max' => 100],
            ['termin_reihe, gremium_id, ba_nr, termin, termin_prev_id, termin_next_id, sitzungsort, referat, referent, vorsitz, wahlperiode, sitzungsstand, created, modified', 'safe'],
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
            'antraegeDokumente'   => [self::HAS_MANY, 'Dokument', 'termin_id'],
            'tagesordnungspunkte' => [self::HAS_MANY, 'Tagesordnungspunkt', 'sitzungstermin_id'],
            'antraegeOrte'        => [self::HAS_MANY, 'AntragOrt', 'termin_id'],
            'gremium'             => [self::BELONGS_TO, 'Gremium', 'gremium_id'],
            'ba'                  => [self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                     => 'ID',
            'typ'                    => 'Typ',
            'datum_letzte_aenderung' => 'Datum Letzte Aenderung',
            'termin_reihe'           => 'Termin Reihe',
            'gremium_id'             => 'Gremium',
            'ba_nr'                  => 'Ba Nr',
            'termin'                 => 'Termin',
            'termin_prev_id'         => 'Termin Prev',
            'termin_next_id'         => 'Termin Next',
            'sitzungsort'            => 'Sitzungsort',
            'referat'                => 'Referat',
            'referent'               => 'Referent',
            'vorsitz'                => 'Vorsitz',
            'wahlperiode'            => 'Wahlperiode',
            'status'                 => 'Status',
            'sitzungsstand'          => 'Sitzungsstand',
        ];
    }

    /**
     * @throws CDbException|Exception
     */
    public function copyToHistory()
    {
        $history = new TerminHistory();
        $history->setAttributes($this->getAttributes(), false);
        if ($history->wahlperiode == "") $history->wahlperiode = "?";
        if ($history->status == "") $history->status = "?";
        if ($history->sitzungsort == "") $history->sitzungsort = "?";
        try {
            if (!$history->save()) {
                RISTools::report_ris_parser_error("Termin:moveToHistory Error", print_r($history->getErrors(), true));
                throw new Exception("Fehler");
            }
        } catch (CDbException $e) {
            if (strpos($e->getMessage(), "Duplicate entry") === false) throw $e;
        }

    }

    public function getLink(array $add_params = []): string
    {
        return Yii::app()->createUrl("termine/anzeigen", array_merge(["id" => $this->id], $add_params));
    }


    public function getTypName(): string
    {
        if ($this->ba_nr > 0) return "BA-Termin";
        else return "Stadtratstermin";
    }

    public function getName(bool $kurzfassung = false): string
    {
        if ($this->typ == static::TYP_BUERGERVERSAMMLUNG) return "Bürger*innenversammlung";

        if (!$this->gremium) return "Unbekanntes Gremium";

        if ($kurzfassung) return $this->gremium->name;
        else return $this->gremium->name . " (" . $this->termin . ")";
    }

    public function getDate(): string
    {
        return $this->datum_letzte_aenderung;
    }


    public function getSourceLink(): string
    {
        return RIS_BASE_URL . "sitzung/detail/" . $this->id;
    }

    public function termine_stadtrat_zeitraum(?int $ba_nr, \DateTime $dateFrom, \DateTime $dateTo, bool $asc = true, int $limit = 0)
    {
        $ba_sql = ($ba_nr > 0 ? " = " . IntVal($ba_nr) : " IS NULL ");
        $params = [
            'condition' => 'termin.ba_nr ' . $ba_sql . ' AND termin.typ = ' . IntVal(Termin::TYP_AUTO) .
                ' AND termin >= "' . addslashes($dateFrom->format('Y-m-d')) . ' 00:00:00"' .
                ' AND termin <= "' . addslashes($dateTo->format('Y-m-d')) . ' 23:59:59"',
            'order'     => 'termin ' . ($asc ? "ASC" : "DESC"),
            'with'      => ["gremium"],
            'alias'     => 'termin'
        ];
        if ($limit > 0) $params['limit'] = $limit;
        $this->getDbCriteria()->mergeWith($params);
        return $this;
    }

    /**
     * @return Dokument[]
     */
    public function getDokumente()
    {
        return $this->antraegeDokumente;
    }

    /**
     * @param int $ba_nr
     * @param string $zeit_von
     * @param string $zeit_bis
     * @param int $limit
     * @return $this
     */
    public function neueste_str_protokolle($ba_nr, $zeit_von, $zeit_bis, $limit = 0)
    {
        $ba_sql = "ba_nr IS NULL";

        $params = [
            'condition' => $ba_sql . ' AND datum_letzte_aenderung >= "' . addslashes($zeit_von) . '" AND datum_letzte_aenderung <= "' . addslashes($zeit_bis) . '"',
            'order'     => 'datum DESC',
            'with'      => [
                'antraegeDokumente' => [
                    'condition' => 'name like "%protokoll%" AND datum >= "' . addslashes($zeit_von) . '" AND datum <= "' . addslashes($zeit_bis) . '"',
                ],
            ]];
        if ($limit > 0) $params['limit'] = $limit;
        $this->getDbCriteria()->mergeWith($params);
        return $this;
    }

    /**
     * @param int $ba_nr
     * @param string $zeit_von
     * @param string $zeit_bis
     * @param int $limit
     * @return $this
     */
    public function neueste_ba_dokumente($ba_nr, $zeit_von, $zeit_bis, $limit = 0)
    {
        $ba_sql = "ba_nr = " . IntVal($ba_nr);

        $params = [
            'condition' => $ba_sql . ' AND datum_letzte_aenderung >= "' . addslashes($zeit_von) . '" AND datum_letzte_aenderung <= "' . addslashes($zeit_bis) . '"',
            'order'     => 'datum DESC',
            'with'      => [
                'antraegeDokumente' => [
                    'condition' => 'datum >= "' . addslashes($zeit_von) . '" AND datum <= "' . addslashes($zeit_bis) . '"',
                ],
            ]];
        if ($limit > 0) $params['limit'] = $limit;
        $this->getDbCriteria()->mergeWith($params);
        return $this;
    }

    /**
     * @return Tagesordnungspunkt[]
     */
    public function tagesordnungspunkteSortiert(): array
    {
        $tagesordnungspunkte = $this->tagesordnungspunkte;
        usort($tagesordnungspunkte, function (Tagesordnungspunkt $ergebnis1, Tagesordnungspunkt $ergebnis2) {
            if ($ergebnis1->status == "geheim" && $ergebnis2->status != "geheim") return 1;
            if ($ergebnis1->status != "geheim" && $ergebnis2->status == "geheim") return -1;
            return $ergebnis1->top_pos <=> $ergebnis2->top_pos;
        });
        return $tagesordnungspunkte;
    }


    public function toArr(): array
    {
        $ts = RISTools::date_iso2timestamp($this->termin);
        if (date("Y", $ts) == date("Y")) {
            $datum_long = strftime("%e. %B, %H:%M Uhr", $ts);
        } else {
            $datum_long = strftime("%e. %B %Y, %H:%M Uhr", $ts);
        }
        return [
            "id"         => $this->id,
            "typ"        => $this->typ,
            "link"       => $this->getLink(),
            "datum"      => strftime("%e. %b., %H:%M", $ts),
            "datum_long" => $datum_long,
            "datum_iso"  => $this->termin,
            "datum_ts"   => $ts,
            "abgesagt"   => $this->istAbgesagt(),
            "gremien"    => [],
            "ort"        => $this->sitzungsort,
            "tos"        => [],
            "dokumente"  => $this->antraegeDokumente,
        ];
    }


    /**
     * @var Termin[] $appointments
     * @return array[]
     */
    public static function groupAppointments($appointments)
    {
        $data = [];
        foreach ($appointments as $appointment) {
            $key = $appointment->termin . $appointment->sitzungsort;
            if (!isset($data[$key])) $data[$key] = $appointment->toArr();
            $url = Yii::app()->createUrl("termine/anzeigen", ["termin_id" => $appointment->id]);
            if ($appointment->gremium) {
                if (!isset($data[$key]["gremien"][$appointment->gremium->name])) $data[$key]["gremien"][$appointment->gremium->name] = [];
                $data[$key]["gremien"][$appointment->gremium->name][] = $url;
            }
        }
        foreach ($data as $key => $val) ksort($data[$key]["gremien"]);
        return $data;
    }

    /**
     * @return null|Dokument
     */
    public function errateAktuellsteTagesordnung()
    {
        $tos = [];
        foreach ($this->antraegeDokumente as $dok) {
            $name = $dok->getName(true);
            if (stripos($name, "tagesordnung") !== false || stripos($name, "einladung") !== false) $tos[] = $dok;
        }
        if (count($tos) == 0) return null;
        usort($tos, function ($to1, $to2) {
            /** @var Dokument $to1 */
            /** @var Dokument $to2 */
            $ts1 = RISTools::date_iso2timestamp($to1->datum);
            $ts2 = RISTools::date_iso2timestamp($to2->datum);
            if ($ts1 < $ts2) return 1;
            if ($ts1 > $ts2) return -1;
            return 0;
        });
        return $tos[0];
    }

    /**
     * @return Termin[]
     */
    public function alleTermineDerReihe()
    {

        /** @var Termin[] $alle_termine */
        $alle_termine = [];

        /**
         * @param Termin[] $alle_termine
         * @param Termin $termin
         */
        function termine_add(&$alle_termine, $termin)
        {
            if (isset($alle_termine[$termin->id])) return;
            $alle_termine[$termin->id] = $termin;
            if ($termin->termin_next_id > 0) {
                $next = Termin::model()->findByPk($termin->termin_next_id);
                if ($next) termine_add($alle_termine, $next);
            }
            if ($termin->termin_prev_id > 0) {
                $prev = Termin::model()->findByPk($termin->termin_prev_id);
                if ($prev) termine_add($alle_termine, $prev);
            }
        }

        termine_add($alle_termine, $this);
        usort($alle_termine, function ($termin1, $termin2) {
            /** @var Termin $termin1 */
            /** @var Termin $termin2 */
            $ts1 = RISTools::date_iso2timestamp($termin1->termin);
            $ts2 = RISTools::date_iso2timestamp($termin2->termin);
            if ($ts1 < $ts2) return 1;
            if ($ts1 > $ts2) return -1;
            return 0;
        });

        return $alle_termine;
    }

    public function istAbgesagt(): bool {
        return ($this->sitzungsstand === static::CANCELED_STR);
    }

    /**
     * @return array
     */
    public function getVEventParams()
    {
        $description = "Infoseite: " . SITE_BASE_URL . Yii::app()->createUrl("termine/anzeigen", ["termin_id" => $this->id]);
        foreach ($this->antraegeDokumente as $dok) {
            $description .= "\n" . $dok->getName() . ": " . $dok->getLinkZumOrginal();
        }
        $ende = date("Y-m-d H:i:s", RISTools::date_iso2timestamp($this->termin) + 3600);
        return [
            'SUMMARY'     => $this->getName(true),
            'DTSTART'     => new \DateTime($this->termin, new DateTimeZone("Europe/Berlin")),
            'DTEND'       => new \DateTime($ende, new DateTimeZone("Europe/Berlin")),
            'LOCATION'    => $this->sitzungsort,
            'DESCRIPTION' => $description,
        ];
    }
}
