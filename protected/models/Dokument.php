<?php

/**
 * This is the model class for table "dokumente".
 *
 * The followings are the available columns in table 'dokumente':
 * @property integer $id
 * @property integer $vorgang_id
 * @property string $typ
 * @property integer $antrag_id
 * @property integer $termin_id
 * @property integer $tagesordnungspunkt_id
 * @property integer $rathausumschau_id
 * @property string $url
 * @property integer $deleted
 * @property string $name
 * @property string $name_title
 * @property string $datum
 * @property string $datum_dokument
 * @property string $text_ocr_raw
 * @property string $text_ocr_corrected
 * @property string $text_ocr_garbage_seiten
 * @property string $text_pdf
 * @property string $ocr_von
 * @property integer $seiten_anzahl
 * @property string|null $highlight
 *
 * The followings are the available model relations:
 * @property Antrag $antrag
 * @property Termin $termin
 * @property Tagesordnungspunkt $tagesordnungspunkt
 * @property AntragOrt[] $orte
 * @property Vorgang $vorgang
 *
 * Scope methods
 * @method boolean getDefaultScopeDisabled()
 * @method Dokument disableDefaultScope()
 */
class Dokument extends CActiveRecord implements IRISItem
{

    public const TYP_STADTRAT_ANTRAG    = "stadtrat_antrag";
    public const TYP_STADTRAT_VORLAGE   = "stadtrat_vorlage";
    public const TYP_STADTRAT_TERMIN    = "stadtrat_termin";
    public const TYP_STADTRAT_BESCHLUSS = "stadtrat_beschluss";
    public const TYP_BA_ANTRAG          = "ba_antrag";
    public const TYP_BA_INITIATIVE      = "ba_initiative";
    public const TYP_BA_TERMIN          = "ba_termin";
    public const TYP_BA_BESCHLUSS       = "ba_beschluss";
    //public static $TYP_BUERGERVERSAMMLUNG_EMPFEHLUNG = "bv_empfehlung"; @TODO
    public const TYPEN_ALLE = [
        "stadtrat_antrag"    => "Stadtratsantrag",
        "stadtrat_vorlage"   => "Stadtratsvorlage",
        "stadtrat_termin"    => "Stadtrat: Termin",
        "stadtrat_beschluss" => "Stadtratsbeschluss",
        "ba_antrag"          => "BA: Antrag",
        "ba_initiative"      => "BA: Initiative",
        "ba_termin"          => "BA: Termin",
        "ba_beschluss"       => "BA: Beschluss",
        "rathausumschau"     => "Rathausumschau",
        //"bv_empfehlung"      => "BürgerInnenversammlung: Empfehlung",
    ];

    public const OCR_VON_TESSERACT = "tesseract";

    private static $_cache = [];

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Dokument the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function getDateiInhalt(): ?string
    {
        try {
            $str = ris_download_string($this->getLinkZumOrginal());
            $f = finfo_open();
            $mime_type = finfo_buffer($f, $str, FILEINFO_MIME_TYPE);
            Header('Content-Type: ' . $mime_type);
            return $str;
        } catch (Exception $e) {
            $fp = fopen(TMP_PATH . "ris-file-not-found.log", "a");
            fwrite($fp, $this->id . " - " . $this->getLinkZumOrginal() . "\n");
            fclose($fp);
            return null;
        }
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'dokumente';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['id, url, name, datum', 'required'],
            ['id, antrag_id, termin_id, tagesordnungspunkt_id, rathausumschau_id, deleted, seiten_anzahl, vorgang_id', 'numerical', 'integerOnly' => true],
            ['typ', 'length', 'max' => 25],
            ['url', 'length', 'max' => 500],
            ['name, name_title', 'length', 'max' => 300],
            ['text_ocr_raw, text_ocr_corrected, text_ocr_garbage_seiten, text_pdf, ocr_von, highlight, name, name_title, created, modified', 'safe'],
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
            'vorgang_id'         => [self::BELONGS_TO, 'Vorgang', 'id'],
            'antrag'             => [self::BELONGS_TO, 'Antrag', 'antrag_id'],
            'termin'             => [self::BELONGS_TO, 'Termin', 'termin_id'],
            'tagesordnungspunkt' => [self::BELONGS_TO, 'Tagesordnungspunkt', 'tagesordnungspunkt_id'],
            'rathausumschau'     => [self::BELONGS_TO, 'Rathausumschau', 'rathausumschau_id'],
            'orte'               => [self::HAS_MANY, 'AntragOrt', 'dokument_id'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id'                      => 'ID',
            'vorgang_id'              => 'Vorgangs-ID',
            'typ'                     => 'Typ',
            'antrag_id'               => 'Antrag',
            'termin_id'               => 'Termin',
            'tagesordnungspunkt_id'   => 'Tagesordnungspunkt',
            'rathausumschau_id'       => 'Rathausumschau',
            'url'                     => 'Url',
            'deleted'                 => 'Gelöscht',
            'name'                    => 'Name',
            'name_title'              => 'Name (Title)',
            'datum'                   => 'Datum',
            'datum_dokument'          => 'Dokumentendatum',
            'text_ocr_raw'            => 'Text Ocr Raw',
            'text_ocr_corrected'      => 'Text Ocr Corrected',
            'text_ocr_garbage_seiten' => 'Text Ocr Garbage Seiten',
            'text_pdf'                => 'Text Pdf',
            'seiten_anzahl'           => 'Seitenanzahl',
        ];
    }

    public function behaviors()
    {
        return [
            'CTimestampBehavior' => [
                'class' => 'DisableDefaultScopeBehavior',
            ]
        ];
    }

    public function defaultScope()
    {
        $alias = $this->getTableAlias(false, false);
        return $this->getDefaultScopeDisabled() ? [] : [
            'condition' => $alias . ".deleted = 0",
        ];

    }

    /**
     * @param int $dokument_id
     * @return Dokument|null
     */
    public static function getCachedByID($dokument_id)
    {
        if (!isset(static::$_cache[$dokument_id])) static::$_cache[$dokument_id] = Dokument::model()->findByPk($dokument_id);
        return static::$_cache[$dokument_id];
    }


    /**
     * @param int $limit
     * @return $this
     */
    public function neueste($limit)
    {
        $this->getDbCriteria()->mergeWith([
            'order' => 'datum DESC',
            'limit' => $limit,
        ]);
        return $this;
    }

    public function getLink(array $add_params = []): string
    {
        return Yii::app()->createUrl("index/dokumente", ["id" => $this->id]);
    }

    public function getLinkZumOrginal(): string
    {
        return RIS_PDF_PREFIX . $this->id;
    }

    public function getDownloadLink(): string
    {
        return Yii::app()->createUrl("index/dokumente", ["id" => $this->id]) . '/datei';
    }

    public function getTypName(): string
    {
        return "Dokument";
    }

    public function getName(bool $langfassung = false): string
    {
        $name = RISTools::korrigiereDokumentenTitel($this->name);
        $name_titel = RISTools::korrigiereDokumentenTitel($this->name_title);
        if ($langfassung) {
            if ($name == "Deckblatt VV") return "Deckblatt (Vollversammlung)";
        } else {
            $name = preg_replace("/^[ 0-9\.]{6,8}/siu", "", $name);

            if ($name_titel == "Antwortschreiben") return "Antwortschreiben";
            if (preg_match("/^Antwortschreiben .*/siu", $name_titel)) return "Antwortschreiben";
            if (preg_match("/^Antwort \\d{2}\-/siu", $name_titel)) return "Antwortschreiben";

            if (strlen($name) > 255) {
                return "Dokument";
            }

            // Prüfen, ob das Dokument und der Antrag den gleichen Namen haben
            if (strlen($name) > 20 && $this->antrag && strlen($this->antrag->getName()) <= 255 && levenshtein($name, $this->antrag->getName()) < 4) return "Dokument";

            $name = preg_replace("/ Nr\. [0-9-]{5} \/ [A-Z] [0-9]+$/siu", "", $name);
            if (preg_match("/^Antwortschreiben .*/siu", $name)) return "Antwortschreiben";
            if (preg_match("/^Antwort \\d{2}\-/siu", $name)) return "Antwortschreiben";
        }
        return $name;
    }

    public function getDateiname(): string
    {
        return $this->id . ' - ' . CHtml::encode($this->getName()) . '.pdf';
    }

    public function getDate(): string
    {
        $ts = RISTools::date_iso2timestamp($this->datum);
        if ($ts > DOCUMENT_DATE_ACCURATE_SINCE || $this->datum_dokument == 0) return $this->datum;
        return $this->datum_dokument;
    }

    public function getDisplayDate(string $fallback = ""): string
    {
        $ts = RISTools::date_iso2timestamp($this->datum);
        if ($ts > DOCUMENT_DATE_ACCURATE_SINCE) return date("d.m.Y", $ts);

        $ts = RISTools::date_iso2timestamp($this->datum_dokument);
        if ($ts > DOCUMENT_DATE_UNKNOWN_BEFORE) return date("d.m.Y", $ts);

        if ($fallback != "") return $fallback;

        return "Vor 2008";
    }

    public function download_if_necessary(): void
    {
        $filename = $this->getLocalPath();
        if (file_exists($filename)) return;

        if (str_starts_with($this->url, "http://") || str_starts_with($this->url, "https://")) {
            CurlBasedDownloader::getInstance()->downloadFile($this->url, $filename);
        } else {
            CurlBasedDownloader::getInstance()->downloadFile(RIS_PDF_PREFIX . $this->id, $filename);
        }
    }

    public function download_and_parse(): void
    {
        $this->download_if_necessary();
        $absolute_filename = $this->getLocalPath();
        if (!file_exists($absolute_filename)) {
            echo "Not Found: " . $this->id . "\n";
            return;
        }

        $metadata             = RISPDF2Text::document_pdf_metadata($absolute_filename);
        $this->seiten_anzahl  = $metadata["pages"];
        $this->datum_dokument = $metadata["date"];
        if ($this->datum_dokument == "") {
            $this->datum_dokument = null;
        }

        $this->text_pdf = "";
        try {
            $this->text_pdf = RISPDF2Text::document_text_pdf($absolute_filename);
        } catch (\Exception $e) {}

        $this->text_ocr_raw       = RISPDF2Text::document_text_ocr($absolute_filename, $this->seiten_anzahl);
        $this->text_ocr_corrected = RISPDF2Text::ris_ocr_clean($this->text_ocr_raw);
        $this->ocr_von            = Dokument::OCR_VON_TESSERACT;
    }

    /**
     * @throws Exception
     */
    public function geo_extract(): void
    {
        $text              = $this->text_ocr_corrected . $this->text_pdf;

        $strassen_gefunden = RISGeo::suche_strassen($text);

        /** @var array|AntragOrt[] $bisherige */
        $bisherige     = AntragOrt::model()->findAllByAttributes(["dokument_id" => $this->id]);
        $bisherige_ids = [];
        foreach ($bisherige as $i) $bisherige_ids[] = $i->ort_id;

        $neue_ids = [];
        $indexed  = [];

        foreach ($strassen_gefunden as $strasse_name) if (!in_array($strasse_name, $indexed)) {
            $indexed[] = $strasse_name;
            $geo       = OrtGeo::getOrCreate($strasse_name);
            if (is_null($geo)) continue;

            $neue_ids[] = $geo->id;

            if (!in_array($geo->id, $bisherige_ids)) {
                $antragort                    = new AntragOrt();
                $antragort->antrag_id         = $this->antrag_id;
                $antragort->termin_id         = $this->termin_id;
                $antragort->rathausumschau_id = $this->rathausumschau_id;
                $antragort->dokument_id       = $this->id;
                $antragort->ort_name          = $strasse_name;
                $antragort->ort_id            = $geo->id;
                $antragort->source            = "text_parse";
                $antragort->datum             = date("Y-m-d H:i:s");
                try {
                    if (!$antragort->save()) {
                        RISTools::report_ris_parser_error("Dokument:geo_extract Error", print_r($antragort->getErrors(), true));
                        throw new Exception("Fehler beim Speichern: geo_extract");
                    }
                } catch (Exception $e) {
                    var_dump($antragort->getAttributes());
                    die();
                }
            }
        }

        foreach ($bisherige_ids as $id) if (!in_array($id, $neue_ids)) {
            AntragOrt::model()->deleteAllByAttributes(["dokument_id" => $this->id, "ort_id" => $id]);
        }

        $this->orte = AntragOrt::model()->findAllByAttributes(["dokument_id" => $this->id]);
    }


    public function reDownloadIndex(): void
    {
        $this->download_and_parse();
        $this->save();
        $this->geo_extract();
        $this->solrIndex();
    }


    /**
     * @param Antrag|Termin|Tagesordnungspunkt $antrag_termin_tagesordnungspunkt
     * @throws Exception
     */
    public static function create_if_necessary(string $typ, $antrag_termin_tagesordnungspunkt, DokumentLink $dok): string
    {
        /** @var Dokument|null $dokument */
        $dokument = Dokument::model()->disableDefaultScope()->findByPk($dok->id);
        if ($dokument) {

            if ($dokument->name != $dok->title || $dokument->name_title != $dok->title) {
                echo "- Dokumententitel geändert: " . $dokument->name . " (" . $dokument->name_title . ")";
                echo "=> " . $dok->title . "\n";
                $dokument->name       = $dok->title;
                $dokument->name_title = $dok->title;
                $dokument->save();
            }

            if (is_a($antrag_termin_tagesordnungspunkt, Antrag::class) && $dokument->antrag_id != $antrag_termin_tagesordnungspunkt->id) {
                echo "- Dokumentenzuordnung korrigiert: Antrag " . $antrag_termin_tagesordnungspunkt->id . "\n";
                $dokument->antrag_id = $antrag_termin_tagesordnungspunkt->id;
                $dokument->save();
            }
            if (is_a($antrag_termin_tagesordnungspunkt, Termin::class) && $dokument->termin_id != $antrag_termin_tagesordnungspunkt->id) {
                echo "- Dokumentenzuordnung korrigiert: Termin " . $antrag_termin_tagesordnungspunkt->id . "\n";
                $dokument->termin_id = $antrag_termin_tagesordnungspunkt->id;
                $dokument->save();
            }
            if (is_a($antrag_termin_tagesordnungspunkt, Tagesordnungspunkt::class) && $dokument->tagesordnungspunkt_id != $antrag_termin_tagesordnungspunkt->id) {
                echo "- Dokumentenzuordnung korrigiert: Tagesordnungspunkt " . $antrag_termin_tagesordnungspunkt->id . "\n";
                $dokument->tagesordnungspunkt_id = $antrag_termin_tagesordnungspunkt->id;
                $dokument->save();
            }

            return "";
        }

        $dokument             = new Dokument();
        $dokument->id         = $dok->id;
        $dokument->typ        = $typ;
        $dokument->datum_dokument = null;
        if (is_a($antrag_termin_tagesordnungspunkt, Antrag::class)) $dokument->antrag_id = $antrag_termin_tagesordnungspunkt->id;
        if (is_a($antrag_termin_tagesordnungspunkt, Termin::class)) $dokument->termin_id = $antrag_termin_tagesordnungspunkt->id;
        if (is_a($antrag_termin_tagesordnungspunkt, Tagesordnungspunkt::class)) $dokument->tagesordnungspunkt_id = $antrag_termin_tagesordnungspunkt->id;
        $dokument->url     = $dok->id;
        $dokument->name    = $dok->title;
        $dokument->name_title = $dok->title;
        $dokument->datum   = date("Y-m-d H:i:s");
        $dokument->deleted = 0;
        $dokument->download_and_parse();

        if (!$dokument->save()) {
            RISTools::report_ris_parser_error("Dokument:create_if_necessary Error", print_r($dokument->getErrors(), true));
            throw new Exception("Fehler");
        }

        $dokument->geo_extract();
        $dokument->solrIndex();

        return "Neue Datei: " . $dok->id . " / " . $dok->title . "\n";
    }


    public function getOriginalLink(): string
    {
        return RIS_PDF_PREFIX . $this->id;
    }

    public function getLocalPath(): string
    {
        $x = explode(".", $this->url);
        $extension = $x[count($x) - 1];

        return PATH_PDF . ($this->id % 100) . "/" . $this->id . "." . $extension;
    }

    private static array $dokumente_cache = [];

    public static function getDocumentBySolrId(string $id, bool $cached = false): ?Dokument
    {
        $x  = explode(":", $id);
        $id = IntVal($x[1]);
        if ($cached) {
            if (!isset(static::$dokumente_cache[$id])) static::$dokumente_cache[$id] = Dokument::model()->with("antrag")->findByPk($id);
            return static::$dokumente_cache[$id];
        }
        return Dokument::model()->with(["antrag", "tagesordnungspunkt"])->findByPk($id);
    }

    public function getRISItem(): ?IRISItem
    {
        if (in_array($this->typ, [static::TYP_STADTRAT_BESCHLUSS, static::TYP_BA_BESCHLUSS])) return $this->tagesordnungspunkt;
        if (in_array($this->typ, [static::TYP_STADTRAT_TERMIN, static::TYP_BA_TERMIN])) return $this->termin;
        return $this->antrag;
    }

    /**
     * Dokument[]
     */
    public function solrMoreLikeThis(int $limit = 10): array
    {
        if ($GLOBALS["SOLR_CONFIG"] === null) return [];
        $solr   = RISSolrHelper::getSolrClient();
        $select = $solr->createSelect();
        $select->setQuery("id:\"Document:" . $this->id . "\"");
        $select->getMoreLikeThis()->setFields("text")->setMinimumDocumentFrequency(1)->setMinimumTermFrequency(1)->setCount($limit);
        $ergebnisse = $solr->select($select);
        $mlt        = $ergebnisse->getMoreLikeThis();
        $ret        = [];
        foreach ($ergebnisse as $document) {
            $mltResult = $mlt->getResult($document->id);
            if ($mltResult) foreach ($mltResult as $mltDoc) {
                $mod = Dokument::model()->findByPk(str_replace("Document:", "", $mltDoc->id));
                if ($mod) $ret[] = $mod;
            }
        }
        return $ret;
    }


    private function solrIndex_termin_do(Solarium\QueryType\Update\Query\Query $update): void
    {
        $gremienname = ($this->termin->gremium ? $this->termin->gremium->name : "");
        /** @var RISSolrDocument $doc */
        $doc                     = $update->createDocument();
        $doc->id                 = "Document:" . $this->id;
        $doc->text               = RISSolrHelper::string_cleanup($gremienname . " " . $this->text_pdf);
        $doc->text_ocr           = RISSolrHelper::string_cleanup($this->text_ocr_corrected);
        $doc->dokument_name      = RISSolrHelper::string_cleanup($this->name);
        $doc->dokument_url       = RISSolrHelper::string_cleanup($this->url);
        $doc->antrag_wahlperiode = RISSolrHelper::string_cleanup($this->termin->wahlperiode);
        $doc->antrag_typ         = ($this->termin->ba_nr > 0 ? "ba_termin" : "stadtrat_termin");
        $doc->antrag_ba          = IntVal($this->termin->ba_nr);
        $doc->antrag_id          = $this->termin->id;
        $doc->antrag_betreff     = RISSolrHelper::string_cleanup($gremienname);
        $doc->termin_datum       = RISSolrHelper::mysql2solrDate($this->termin->termin);
        $max_datum               = $this->termin->termin;

        $geo            = [];
        $dokument_bas   = [];
        $dokument_bas[] = ($this->termin->ba_nr > 0 ? $this->termin->ba_nr : 0);
        foreach ($this->orte as $ort) if ($ort->ort->to_hide == 0) {
            $geo[] = $ort->ort->lat . "," . $ort->ort->lon;
            if ($ort->ort->ba_nr > 0 && !in_array($ort->ort->ba_nr, $dokument_bas)) $dokument_bas[] = $ort->ort->ba_nr;
        }
        $doc->geo          = $geo;
        $doc->dokument_bas = $dokument_bas;

        $aenderungs_datum = [];

        /** @var array|RISAenderung[] $aenderungen */
        $aenderungen = RISAenderung::model()->findAllByAttributes(["ris_id" => $this->antrag_id], ["order" => "datum DESC"]);
        foreach ($aenderungen as $o) {
            $aenderungs_datum[] = RISSolrHelper::mysql2solrDate($o->datum);
            $max_datum          = $o->datum;
        }

        $doc->aenderungs_datum = $aenderungs_datum;

        if ($max_datum != "") $doc->sort_datum = RISSolrHelper::mysql2solrDate($max_datum);

        $update->addDocuments([$doc]);

    }


    private function solrIndex_antrag_do(\Solarium\QueryType\Update\Query\Query $update): void
    {
        if (!$this->antrag) return;

        $max_datum = "";
        /** @var RISSolrDocument $doc */
        $doc = $update->createDocument();

        $doc->id                 = "Document:" . $this->id;
        $doc->text               = RISSolrHelper::string_cleanup(RISTools::normalizeTitle($this->antrag->betreff) . " " . $this->text_pdf);
        $doc->text_ocr           = RISSolrHelper::string_cleanup($this->text_ocr_corrected);
        $doc->dokument_name      = RISSolrHelper::string_cleanup($this->name);
        $doc->dokument_url       = $this->url;
        $doc->antrag_nr          = $this->antrag->antrags_nr;
        $doc->antrag_wahlperiode = $this->antrag->wahlperiode;
        $doc->antrag_typ         = $this->antrag->typ;
        $doc->antrag_ba          = $this->antrag->ba_nr;
        $doc->antrag_id          = $this->antrag->id;
        $doc->antrag_betreff     = RISSolrHelper::string_cleanup($this->antrag->betreff);
        $doc->referat_id         = $this->antrag->referat_id;

        $antrag_erstellt = $aenderungs_datum = [];
        if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $this->antrag->gestellt_am)) {
            $antrag_erstellt[]  = RISSolrHelper::mysql2solrDate($this->antrag->gestellt_am . " 12:00:00");
            $aenderungs_datum[] = RISSolrHelper::mysql2solrDate($this->antrag->gestellt_am . " 12:00:00");
            $max_datum          = $this->antrag->gestellt_am . " 12:00:00";
        }


        $geo            = [];
        $dokument_bas   = [];
        $dokument_bas[] = ($this->antrag->ba_nr > 0 ? $this->antrag->ba_nr : 0);
        foreach ($this->orte as $ort) if ($ort->ort->to_hide == 0) {
            $geo[] = $ort->ort->lat . "," . $ort->ort->lon;
            if ($ort->ort->ba_nr > 0 && !in_array($ort->ort->ba_nr, $dokument_bas)) $dokument_bas[] = $ort->ort->ba_nr;
        }
        $doc->geo          = $geo;
        $doc->dokument_bas = $dokument_bas;

        /** @var array|RISAenderung[] $aenderungen */
        $aenderungen = RISAenderung::model()->findAllByAttributes(["ris_id" => $this->antrag_id], ["order" => "datum DESC"]);
        foreach ($aenderungen as $o) {
            $aenderungs_datum[] = RISSolrHelper::mysql2solrDate($o->datum);
            $max_datum          = $o->datum;
        }

        $doc->antrag_erstellt     = $antrag_erstellt;
        $doc->aenderungs_datum    = $aenderungs_datum;
        $doc->antrag_gestellt_von = RISSolrHelper::string_cleanup($this->antrag->gestellt_von . " " . $this->antrag->initiatorInnen);


        if ($max_datum != "") $doc->sort_datum = RISSolrHelper::mysql2solrDate($max_datum);
        $update->addDocuments([$doc]);
    }

    private function solrIndex_generic_do($doc, $name_id, $text, $datum)
    {
        $doc->id            = $name_id . $this->id;
        $doc->text          = RISSolrHelper::string_cleanup($text);
        $doc->text_ocr      = RISSolrHelper::string_cleanup($this->text_ocr_corrected);
        $doc->dokument_name = RISSolrHelper::string_cleanup($this->name);
        $doc->dokument_url  = $this->url;

        $geo          = [];
        $dokument_bas = [];
        foreach ($this->orte as $ort) if ($ort->ort->to_hide == 0) {
            $geo[] = $ort->ort->lat . "," . $ort->ort->lon;
            if ($ort->ort->ba_nr > 0 && !in_array($ort->ort->ba_nr, $dokument_bas)) $dokument_bas[] = $ort->ort->ba_nr;
        }
        $doc->geo          = $geo;
        $doc->dokument_bas = $dokument_bas;
        $doc->sort_datum = RISSolrHelper::mysql2solrDate($datum);
    }

    /**
     * @param Solarium\QueryType\Update\Query\Query $update
     */
    private function solrIndex_beschluss_do($update)
    {
        /** @var RISSolrDocument $doc */
        $doc = $update->createDocument();

        if (is_null($this->tagesordnungspunkt)) return; // Kann vorkommen, wenn ein TOP nachträglich gelöscht wurde

        $text = $this->tagesordnungspunkt->top_betreff . " " . $this->tagesordnungspunkt->beschluss_text . " " . $this->tagesordnungspunkt->entscheidung . " " . $this->text_pdf;
        $this->solrIndex_generic_do($doc, "Ergebnis:", $text, $this->getDate());

        $update->addDocuments([$doc]);
    }


    public function solrIndex(): void
    {
        $tries = 3;
        while ($tries > 0) try {
            $solr   = RISSolrHelper::getSolrClient();
            $update = $solr->createUpdate();

            if ($this->deleted == 1) {
                $update->addDeleteQuery("id:\"Document:" . $this->id . "\"");
            } else {
                if (in_array($this->typ, [static::TYP_STADTRAT_TERMIN, static::TYP_BA_TERMIN])) $this->solrIndex_termin_do($update);
                elseif (in_array($this->typ, [static::TYP_STADTRAT_BESCHLUSS, static::TYP_BA_BESCHLUSS])) $this->solrIndex_beschluss_do($update);
                else $this->solrIndex_antrag_do($update);
            }
            $update->addCommit();
            $solr->update($update);
            return;
        } catch (\Throwable $e) {
            $tries--;
            //sleep(15);
            RISTools::report_ris_parser_error("Failed Indexing: " . $e->getMessage(), '');
        }
    }

    /**
     * @param int $limit
     * @return Dokument[]
     */
    public static function getHighlightDokumente($limit = 3)
    {
        return Dokument::model()->findAll(["condition" => "highlight IS NOT NULL", "order" => "highlight DESC", "limit" => $limit]);
    }

    /**
     * @throws CDbException
     */
    public function delete(): void
    {
        $this->deleted = 1;
        $this->save();

        $this->solrIndex();
        //foreach ($this->orte as $ort) $ort->delete();
    }

    /**
     * @throws CDbException
     */
    public function undelete(): void
    {
        $this->deleted = 0;
        $this->save();

        $this->geo_extract();
        $this->solrIndex();
    }
}
