<?php

/**
 * This is the model class for table "antraege".
 *
 * The followings are the available columns in table 'antraege':
 *
 * @property int $id
 * @property int $vorgang_id
 * @property string $typ
 * @property string $datum_letzte_aenderung
 * @property int $ba_nr
 * @property string $gestellt_am
 * @property string $gestellt_von
 * @property string $antrags_nr
 * @property string $bearbeitungsfrist
 * @property string $registriert_am
 * @property string $erledigt_am
 * @property string $referat
 * @property string $referent
 * @property int $referat_id
 * @property string $wahlperiode
 * @property string $antrag_typ
 * @property string $betreff
 * @property string $kurzinfo
 * @property string $status
 * @property string $bearbeitung
 * @property string $fristverlaengerung
 * @property string $initiatorInnen
 * @property string $initiative_to_aufgenommen
 *
 * The followings are the available model relations:
 * @property Bezirksausschuss $ba
 * @property Dokument[] $dokumente
 * @property Tagesordnungspunkt[] $ergebnisse
 * @property AntragOrt[] $orte
 * @property AntragPerson[] $antraegePersonen
 * @property StadtraetIn[] $stadtraetInnen
 * @property Antrag[] $antrag2vorlagen
 * @property Antrag[] $vorlage2antraege
 * @property Vorgang $vorgang
 * @property Tag[] $tags
 */
class Antrag extends CActiveRecord implements IRISItemHasDocuments
{
    public static $TYP_STADTRAT_ANTRAG               = "stadtrat_antrag";
    public static $TYP_STADTRAT_VORLAGE              = "stadtrat_vorlage";
    public static $TYP_STADTRAT_VORLAGE_GEHEIM       = "stadtrat_vorlage_geheim";
    public static $TYP_BA_ANTRAG                     = "ba_antrag";
    public static $TYP_BA_INITIATIVE                 = "ba_initiative";
    public static $TYP_BUERGERVERSAMMLUNG_EMPFEHLUNG = "bv_empfehlung";

    public static $TYPEN_ALLE = [
        "stadtrat_antrag"         => "Stadtratsantrag|Stadtratsanträge",
        "stadtrat_vorlage"        => "Stadtratsvorlage|Stadtratsvorlagen",
        "ba_antrag"               => "BA-Antrag|BA-Anträge",
        "ba_initiative"           => "BA-Initiative|BA-Initiativen",
        "stadtrat_vorlage_geheim" => "Geheime Stadtratsvorlage|Geheime Stadtratsvorlagen",
        "bv_empfehlung"           => "BV-Empfehlung|BV-Empfehlungen",
    ];

    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     *
     * @return Antrag the static model class
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
        return 'antraege';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['id, typ, datum_letzte_aenderung, antrags_nr, wahlperiode, betreff, status', 'required'],
            ['id, ba_nr, vorgang_id, referat_id', 'numerical', 'integerOnly' => true],
            ['typ', 'length', 'max' => 16],
            ['antrags_nr', 'length', 'max' => 20],
            ['referat', 'length', 'max' => 500],
            ['referent', 'length', 'max' => 200],
            ['wahlperiode, antrag_typ, status', 'length', 'max' => 50],
            ['bearbeitung', 'length', 'max' => 100],
            ['typ, ba_nr, antrags_nr, bearbeitungsfrist, registriert_am, erledigt_am, referat, referent, wahlperiode, antrag_typ, betreff, kurzinfo, status, bearbeitung, fristverlaengerung, initiatorInnen, initiative_to_aufgenommen', 'safe'],
            ['typ, datum_letzte_aenderung, ba_nr, gestellt_am, gestellt_von, antrags_nr, bearbeitungsfrist, registriert_am, erledigt_am, referat, referent, wahlperiode, antrag_typ, betreff, kurzinfo, status, bearbeitung, fristverlaengerung, initiatorInnen, initiative_to_aufgenommen', 'safe', 'on' => 'insert'],
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
            'ba'               => [self::BELONGS_TO, 'Bezirksausschuss', 'ba_nr'],
            'dokumente'        => [self::HAS_MANY, 'Dokument', 'antrag_id'],
            'ergebnisse'       => [self::HAS_MANY, 'Tagesordnungspunkt', 'antrag_id'],
            //'antraege_links_in' => array(self::HAS_MANY, 'Antrag', 'antrag1'),
            //'antraege_links_out' => array(self::HAS_MANY, 'AntraegeLinks', 'antrag2'),
            'orte'             => [self::HAS_MANY, 'AntragOrt', 'antrag_id'],
            'antraegePersonen' => [self::HAS_MANY, 'AntragPerson', 'antrag_id'],
            'stadtraetInnen'   => [self::MANY_MANY, 'StadtraetIn', 'antraege_stadtraetInnen(antrag_id, stadtraetIn_id)'],
            'vorlage2antraege' => [self::MANY_MANY, 'Antrag', 'antraege_vorlagen(antrag1, antrag2)'],
            'antrag2vorlagen'  => [self::MANY_MANY, 'Antrag', 'antraege_vorlagen(antrag2, antrag1)'],
            'abos'             => [self::MANY_MANY, 'AntragAbo', 'antraege_abos(antrag_id, benutzerIn_id)'],
            'vorgang'          => [self::BELONGS_TO, 'Vorgang', 'vorgang_id'],
            'tags'             => [self::MANY_MANY, 'Tag', 'antraege_tags(antrag_id, tag_id)'],
        ];
    }

    /**
     * @param Antrag $vorlage
     */
    public function addVorlage($vorlage)
    {
        try {
            Yii::app()->db->createCommand()->insert("antraege_vorlagen", ["antrag1" => $vorlage->id, "antrag2" => $this->id]);
            $this->antrag2vorlagen     = array_merge($this->antrag2vorlagen, [$vorlage]);
            $vorlage->vorlage2antraege = array_merge($vorlage->vorlage2antraege, [$this]);
        } catch (Exception $e) {
        }
    }

    /**
     * @param Antrag $antrag
     */
    public function addAntrag($antrag)
    {
        try {
            Yii::app()->db->createCommand()->insert("antraege_vorlagen", ["antrag2" => $antrag->id, "antrag1" => $this->id]);
            $this->vorlage2antraege  = array_merge($this->vorlage2antraege, [$antrag]);
            $antrag->antrag2vorlagen = array_merge($antrag->antrag2vorlagen, [$this]);
        } catch (Exception $e) {
        }
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                        => 'ID',
            'vorgang_id'                => 'Vorgangs-ID',
            'typ'                       => 'Typ',
            'datum_letzte_aenderung'    => 'Datum Letzte Aenderung',
            'ba_nr'                     => 'Ba Nr',
            'gestellt_am'               => 'Gestellt Am',
            'gestellt_von'              => 'Gestellt Von',
            'antrags_nr'                => 'Antrags Nr',
            'bearbeitungsfrist'         => 'Bearbeitungsfrist',
            'registriert_am'            => 'Registriert Am',
            'erledigt_am'               => 'Erledigt Am',
            'referat'                   => 'Referat',
            'referent'                  => 'Referent',
            'referat_id'                => 'Referat-ID',
            'wahlperiode'               => 'Wahlperiode',
            'antrag_typ'                => 'Antrag Typ',
            'betreff'                   => 'Betreff',
            'kurzinfo'                  => 'Kurzinfo',
            'status'                    => 'Status',
            'bearbeitung'               => 'Bearbeitung',
            'fristverlaengerung'        => 'Fristverlaengerung',
            'initiatorInnen'            => 'InitiatorInnen',
            'initiative_to_aufgenommen' => 'Initiative To Aufgenommen',
        ];
    }

    /**
     * @param null|int $ba_nr
     * @param string   $zeit_von
     * @param string   $zeit_bis
     * @param int      $limit
     *
     * @return $this
     */
    public function neueste_stadtratsantragsdokumente($ba_nr, $zeit_von, $zeit_bis, $limit = 0)
    {
        $ba_sql = ($ba_nr > 0 ? " = ".intval($ba_nr) : " IS NULL");
        $params = [
            'condition' => 'ba_nr '.$ba_sql.' AND datum_letzte_aenderung >= "'.addslashes($zeit_von).'"',
            'order'     => 'datum DESC',
            'with'      => [
                'dokumente' => [
                    'condition' => 'datum >= "'.addslashes($zeit_von).'" AND datum <= "'.addslashes($zeit_bis).'"',
                ],
            ], ];
        if ($limit > 0) $params['limit'] = $limit;
        $this->getDbCriteria()->mergeWith($params);

        return $this;
    }

    /**
     * @param null|int $ba_nr
     * @param string   $zeit_von
     * @param string   $zeit_bis
     * @param int      $limit
     *
     * @return $this
     */
    public function neueste_stadtratsantragsdokumente_geo($ba_nr, $zeit_von, $zeit_bis, $limit = 0)
    {
        $params = [
            'alias'     => 'a',
            'condition' => 'a.datum_letzte_aenderung >= "'.addslashes($zeit_von).'"',
            'order'     => 'b.datum DESC',
            'with'      => [
                'dokumente'          => [
                    'alias'     => 'b',
                    'condition' => 'b.datum >= "'.addslashes($zeit_von).'" AND b.datum <= "'.addslashes($zeit_bis).'"',
                ],
                'dokumente.orte'     => [],
                'dokumente.orte.ort' => [
                    'alias'     => 'c',
                    'condition' => 'c.ba_nr = '.intval($ba_nr).' AND c.to_hide = 0',
                ],
            ], ];
        if ($limit > 0) $params['limit'] = $limit;
        $this->getDbCriteria()->mergeWith($params);

        return $this;
    }

    /**
     * @param int    $referat_id
     * @param string $zeit_von
     * @param string $zeit_bis
     * @param int    $limit
     *
     * @return $this
     */
    public function neueste_stadtratsantragsdokumente_referat($referat_id, $zeit_von, $zeit_bis, $limit = 0)
    {
        $params = [
            'alias'     => 'a',
            'condition' => 'a.datum_letzte_aenderung >= "'.addslashes($zeit_von).'" AND a.referat_id = '.intval($referat_id),
            'order'     => 'b.datum DESC',
            'with'      => [
                'dokumente' => [
                    'alias'     => 'b',
                    'condition' => 'b.datum >= "'.addslashes($zeit_von).'" AND b.datum <= "'.addslashes($zeit_bis).'"',
                ],
            ], ];
        if ($limit > 0) $params['limit'] = $limit;
        $this->getDbCriteria()->mergeWith($params);

        return $this;
    }

    /**
     * @return int
     */
    public function neuestes_dokument_ts()
    {
        $ret = 0;
        foreach ($this->dokumente as $dok) {
            $ts                  = RISTools::date_iso2timestamp($dok->datum);
            if ($ts > $ret) $ret = $ts;
        }

        return $ret;
    }

    /**
     * @throws CDbException|Exception
     */
    public function copyToHistory()
    {
        $history = new AntragHistory();
        $history->setAttributes($this->getAttributes(), false);
        try {
            if (!$history->save()) {
                RISTools::send_email(Yii::app()->params['adminEmail'], "Antrag:moveToHistory Error", print_r($history->getErrors(), true), null, "system");
                throw new Exception("Fehler");
            }
        } catch (CDbException $e) {
            if (strpos($e->getMessage(), "Duplicate entry") === false) throw $e;
        }

    }

    /**
     * @return HistorienEintragAntrag[]
     */
    public function getHistoryDiffs()
    {
        $histories = [];

        $neu = new AntragHistory();
        $neu->setAttributes($this->getAttributes());

        /**
         * @var AntragHistory[]
         *                      '='M');
         *                      $criteria = new CDbCriteria(array('order'=>'user_date_created DESC','limit'=>10));
         *                      $criteria->addBetweenCondition('user_date_created', $date['date_start'], $date['date_end']);
         *                      $rows = user::model()->findAllByAttributes($u
         */
        $criteria = new CDbCriteria(['order' => "datum_letzte_aenderung DESC"]);
        $criteria->addCondition("datum_letzte_aenderung >= '2014-05-01 00:00:00'");
        $his = AntragHistory::model()->findAllByAttributes(["id" => $this->id], $criteria);
        foreach ($his as $alt) {
            $histories[] = new HistorienEintragAntrag($alt, $neu);
            $neu         = $alt;
        }

        return $histories;
    }

    /**
     * @throws Exception
     */
    public function resetPersonen()
    {
        /** @var array|AntragPerson[] $alte */
        $alte = AntragPerson::model()->findAllByAttributes(["antrag_id" => $this->id]);
        foreach ($alte as $alt) $alt->delete();

        $indexed = [];

        $gestellt_von = RISTools::normalize_antragvon($this->gestellt_von);
        foreach ($gestellt_von as $x) if (!in_array($x["name_normalized"], $indexed)) {
            $indexed[]     = $x["name_normalized"];
            $person        = Person::getOrCreate($x["name"], $x["name_normalized"]);
            $ap            = new AntragPerson();
            $ap->antrag_id = $this->id;
            $ap->person_id = $person->id;
            $ap->typ       = AntragPerson::$TYP_GESTELLT_VON;
            if (!$ap->save()) {
                RISTools::send_email(Yii::app()->params['adminEmail'], "Antrag:resetPersonen Error", print_r($ap->getErrors(), true), null, "system");
                throw new Exception("Fehler");
            }
        }

        $initiatorInnen = RISTools::normalize_antragvon($this->initiatorInnen);
        foreach ($initiatorInnen as $x) if (!in_array($x["name_normalized"], $indexed)) {
            $indexed[]     = $x["name_normalized"];
            $person        = Person::getOrCreate($x["name"], $x["name_normalized"]);
            $ap            = new AntragPerson();
            $ap->antrag_id = $this->id;
            $ap->person_id = $person->id;
            $ap->typ       = AntragPerson::$TYP_INITIATORIN;
            if (!$ap->save()) {
                RISTools::send_email(Yii::app()->params['adminEmail'], "Antrag:resetPersonen Error", print_r($ap->getErrors(), true), null, "system");
                throw new Exception("Fehler");
            }
        }
    }

    /**
     * @param array $add_params
     *
     * @return string
     */
    public function getLink($add_params = [])
    {
        return Yii::app()->createUrl("antraege/anzeigen", array_merge(["id" => $this->id], $add_params));
    }

    /**
     * @return string
     */
    public function getSourceLink()
    {
        switch ($this->typ) {
            case self::$TYP_BA_ANTRAG:
                return "http://www.ris-muenchen.de/RII/BA-RII/ba_antraege_details.jsp?Id=".$this->id."&selTyp=BA-Antrag";
            case self::$TYP_BA_INITIATIVE:
                return "http://www.ris-muenchen.de/RII/BA-RII/ba_initiativen_details.jsp?Id=".$this->id;
            case self::$TYP_STADTRAT_ANTRAG:
                return "http://www.ris-muenchen.de/RII/RII/ris_antrag_detail.jsp?risid=".$this->id;
            case self::$TYP_STADTRAT_VORLAGE:
                return "http://www.ris-muenchen.de/RII/RII/ris_vorlagen_detail.jsp?risid=".$this->id;
        }

        return "";
    }

    /** @return string */
    public function getTypName()
    {
        $str = explode("|", self::$TYPEN_ALLE[$this->typ]);

        return $str[0];
    }

    /**
     * @param bool $kurzfassung
     *
     * @return string
     */
    public function getName($kurzfassung = false)
    {
        if ($kurzfassung) {
            $betreff = str_replace(["\n", "\r"], [" ", " "], $this->betreff);
            $x       = explode(" Antrag Nr.", $betreff);
            $x       = explode(" Änderungsantrag ", $x[0]);
            $x       = explode("<strong>Antrag: </strong>", $x[0]);
            $x       = explode(" Empfehlung Nr.", $x[0]);
            $x       = explode(" BA-Antrags-", $x[0]);

            return RISTools::korrigiereTitelZeichen($x[0]);
        } else {
            return RISTools::korrigiereTitelZeichen($this->betreff);
        }
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->datum_letzte_aenderung;
    }

    /**
     * @return int
     */
    public function getDokumentenMaxTS()
    {
        $max_date = 0;
        foreach ($this->dokumente as $dokument) {
            $dat                            = RISTools::date_iso2timestamp($dokument->getDate());
            if ($dat > $max_date) $max_date = $dat;
        }

        return $max_date;
    }

    /**
     * @return Dokument[]
     */
    public function getDokumente()
    {
        return $this->dokumente;
    }

    /**
     * @param int $anz
     *
     * @return Dokument[]
     */
    public function errateThemenverwandteDokumente($anz)
    {
        /** @var Dokument[] $dokumente */
        $dokumente = [];
        /** @var int[] $dokumente_count */
        $dokumente_count = [];

        foreach ($this->dokumente as $dokument) {
            $related = $dokument->solrMoreLikeThis(count($this->dokumente) + $anz);
            foreach ($related as $rel_dok) if ($rel_dok->antrag_id != $this->id) {
                if (isset($dokumente_count[$rel_dok->id])) {
                    $dokumente_count[$rel_dok->id]++;
                } else {
                    $dokumente[$rel_dok->id]       = $rel_dok;
                    $dokumente_count[$rel_dok->id] = 1;
                }
            }
        }

        arsort($dokumente_count);

        $ret                                                                  = [];
        $i                                                                    = 0;
        foreach ($dokumente_count as $dok_id => $anz) if ($i++ < $anz) $ret[] = $dokumente[$dok_id];

        return $ret;
    }

    /**
     * @param int $limit
     *
     * @return Antrag[]
     */
    public function errateThemenverwandteAntraege($limit)
    {
        /** @var Antrag[] $rel_antraege */
        $rel_antraege = [];
        /** @var int[] $rel_antraege_count */
        $rel_antraege_count = [];

        foreach ($this->dokumente as $dokument) if ($dokument->antrag_id > 0) {
            $related = $dokument->solrMoreLikeThis($limit * 2);
            foreach ($related as $rel_dok) if ($rel_dok->antrag_id > 0 && $rel_dok->antrag_id != $this->id) {
                if (isset($rel_antraege_count[$rel_dok->antrag_id])) {
                    $rel_antraege_count[$rel_dok->antrag_id]++;
                } else {
                    $rel_antraege[$rel_dok->antrag_id]       = $rel_dok->antrag;
                    $rel_antraege_count[$rel_dok->antrag_id] = 1;
                }
            }
        }

        $ret = [];
        $i   = 0;
        foreach ($rel_antraege_count as $ant_id => $anz) {
            if (!ris_intern_antrag_ist_relevant_mlt($this, $rel_antraege[$ant_id])) continue;
            if ($i++ < $limit) $ret[] = $rel_antraege[$ant_id];
        }

        return $ret;
    }

    /**
     * @param Antrag               $curr_antrag
     * @param Antrag[]             $gefundene_antraege
     * @param Tagesordnungspunkt[] $gefundene_tops
     * @param Dokument[]           $gefundene_dokumente
     * @param int                  $vorgang_id
     *
     * @throws Exception
     */
    private static function rebuildVorgaengeRekursiv($curr_antrag, &$gefundene_antraege, &$gefundene_tops, &$gefundene_dokumente, &$vorgang_id)
    {
        /** @var Antrag $antrag */
        foreach (array_merge($curr_antrag->vorlage2antraege, $curr_antrag->antrag2vorlagen) as $ant) if (!isset($gefundene_antraege[$ant->id])) {
            if ($ant->vorgang_id > 0 && $vorgang_id > 0 && $ant->vorgang_id != $vorgang_id) {
                Vorgang::vorgangMerge($ant->vorgang_id, $vorgang_id);
                $ant->vorgang_id = $vorgang_id;
                $ant->save(false);
            }
            if ($ant->vorgang_id > 0) $vorgang_id = $ant->vorgang_id;
            $gefundene_antraege[$ant->id]         = $ant;
            static::rebuildVorgaengeRekursiv($ant, $gefundene_antraege, $gefundene_tops, $gefundene_dokumente, $vorgang_id);
        }
        foreach ($curr_antrag->ergebnisse as $ergebnis) {
            $gefundene_ergebnisse[$ergebnis->id]                                            = $ergebnis;
            foreach ($ergebnis->dokumente as $dokument) $gefundene_dokumente[$dokument->id] = $dokument;
        }
        foreach ($curr_antrag->dokumente as $dokument) $gefundene_dokumente[$dokument->id] = $dokument;
    }

    /**
     *
     */
    public function rebuildVorgaenge()
    {
        //if ($this->vorgang_id > 0) return;
        $vorgang_id = 0;
        /** @var Antrag[] $gefundene_antraege */
        $gefundene_antraege = [];
        /** @var Tagesordnungspunkt[] $gefundene_ergebnisse */
        $gefundene_ergebnisse = [];
        /** @var Dokument[] $gefundene_dokumente */
        $gefundene_dokumente = [];
        try {
            static::rebuildVorgaengeRekursiv($this, $gefundene_antraege, $gefundene_ergebnisse, $gefundene_dokumente, $vorgang_id);
        } catch (Exception $e) {
            var_dump($e);

            return;
        }
        if ($vorgang_id == 0) {
            $vorgang      = new Vorgang();
            $vorgang->typ = 0;
            $vorgang->save(false);
            $vorgang_id = $vorgang->id;

            $gefundene_antraege[] = $this;
        }

        // Allen gefundenen Objekten den richtigen Vorgang zuordnen
        foreach (array_merge($gefundene_antraege, $gefundene_ergebnisse, $gefundene_dokumente) as $gefunden) {
            $gefunden->vorgang_id = $vorgang_id;
            $gefunden->save();
        }
        if (SITE_CALL_MODE == "shell") echo "Fertig";
    }

    /**
     * @return Vorgang
     */
    public function getVorgang()
    {
        if ($this->vorgang === null) $this->rebuildVorgaenge();
        $this->refresh();

        return $this->vorgang;
    }

    /**
     * @param string $antrags_nr
     *
     * @return string
     */
    public static function cleanAntragNr($antrags_nr)
    {
        return preg_replace("/[^a-zA-Z0-9\/-]/siu", "", $antrags_nr);
    }

    /**
     * @param int $von_ts
     * @param int $bis_ts
     *
     * @return RISAenderung[]
     */
    public function findeAenderungen($von_ts, $bis_ts = 0)
    {
        /** @var RISAenderung[] $aenderungen */
        $aenderungen = RISAenderung::model()->findAllByAttributes(["typ" => $this->typ, "ris_id" => $this->id]);
        foreach ($aenderungen as $ae) {
            // @TODO var_dump($ae->getAttributes());
        }
    }

    public function findeFraktionen()
    {
        $parteien = [];
        foreach ($this->antraegePersonen as $person) {
            $name                                        = $person->person->getName(true);
            $partei                                      = $person->person->ratePartei($this->gestellt_am);
            $key                                         = ($partei ? $partei : $name);
            if (!isset($parteien[$key])) $parteien[$key] = [];
            $parteien[$key][]                            = $name;
        }

        $ergebniss = [];
        foreach ($parteien as $partei => $personen) {
            $personen_net                                                = [];
            foreach ($personen as $p) if ($p != $partei) $personen_net[] = $p;
            $ergebniss[]                                                 = ["name" => $partei, "mitglieder" => $personen_net];
        }

        return $ergebniss;
    }
}
