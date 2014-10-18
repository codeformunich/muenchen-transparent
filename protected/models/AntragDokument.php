<?php

/**
 * This is the model class for table "antraege_dokumente".
 *
 * The followings are the available columns in table 'antraege_dokumente':
 * @property integer $id
 * @property integer $vorgang_id
 * @property string $typ
 * @property integer $antrag_id
 * @property integer $termin_id
 * @property integer $ergebnis_id
 * @property string $url
 * @property string $name
 * @property string $datum
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
 * @property AntragErgebnis $ergebnis
 * @property AntragOrt[] $orte
 * @property Vorgang $vorgang
 */
class AntragDokument extends CActiveRecord implements IRISItem
{

	public static $TYP_STADTRAT_ANTRAG = "stadtrat_antrag";
	public static $TYP_STADTRAT_VORLAGE = "stadtrat_vorlage";
	public static $TYP_STADTRAT_TERMIN = "stadtrat_termin";
	public static $TYP_STADTRAT_BESCHLUSS = "stadtrat_beschluss";
	public static $TYP_BA_ANTRAG = "ba_antrag";
	public static $TYP_BA_INITIATIVE = "ba_initiative";
	public static $TYP_BA_TERMIN = "ba_termin";
	public static $TYP_BA_BESCHLUSS = "ba_beschluss";
	public static $TYP_BV_EMPFEHLUNG = "bv_empfehlung";
	public static $TYPEN_ALLE = array(
		"stadtrat_antrag"    => "Stadtratsantrag",
		"stadtrat_vorlage"   => "Stadtratsvorlage",
		"stadtrat_termin"    => "Stadtrat: Termin",
		"stadtrat_beschluss" => "Stadtratsbeschluss",
		"ba_antrag"          => "BA: Antrag",
		"ba_initiative"      => "BA: Initiative",
		"ba_termin"          => "BA: Termin",
		"ba_beschluss"       => "BA: Beschluss",
		"bv_empfehlung"      => "BürgerInnenversammlung: Empfehlung",
	);

	public static $OCR_VON_TESSERACT = "tesseract";
	public static $OCR_VON_OMNIPAGE = "omnipage";

	private static $_cache = array();

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AntragDokument the static model class
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
		return 'antraege_dokumente';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, url, name, datum', 'required'),
			array('id, antrag_id, termin_id, ergebnis_id, seiten_anzahl, vorgang_id', 'numerical', 'integerOnly' => true),
			array('typ', 'length', 'max' => 25),
			array('url', 'length', 'max' => 500),
			array('name', 'length', 'max' => 200),
			array('text_ocr_raw, text_ocr_corrected, text_ocr_garbage_seiten, text_pdf, ocr_von, highlight', 'safe'),
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
			'vorgang_id' => array(self::BELONGS_TO, 'Vorgang', 'id'),
			'antrag'     => array(self::BELONGS_TO, 'Antrag', 'antrag_id'),
			'termin'     => array(self::BELONGS_TO, 'Termin', 'termin_id'),
			'ergebnis'   => array(self::BELONGS_TO, 'AntragErgebnis', 'ergebnis_id'),
			'orte'       => array(self::HAS_MANY, 'AntragOrt', 'dokument_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                      => 'ID',
			'vorgang_id'              => 'Vorgangs-ID',
			'typ'                     => 'Typ',
			'antrag_id'               => 'Antrag',
			'termin_id'               => 'Termin',
			'ergebnis_id'             => 'Ergebnis',
			'url'                     => 'Url',
			'name'                    => 'Name',
			'datum'                   => 'Datum',
			'text_ocr_raw'            => 'Text Ocr Raw',
			'text_ocr_corrected'      => 'Text Ocr Corrected',
			'text_ocr_garbage_seiten' => 'Text Ocr Garbage Seiten',
			'text_pdf'                => 'Text Pdf',
			'seiten_anzahl'           => 'Seitenanzahl',
		);
	}


	/**
	 * @param int $dokument_id
	 * @return AntragDokument|null
	 */
	public static function getCachedByID($dokument_id)
	{
		if (!isset(static::$_cache[$dokument_id])) static::$_cache[$dokument_id] = AntragDokument::model()->findByPk($dokument_id);
		return static::$_cache[$dokument_id];
	}


	/**
	 * @param int $limit
	 * @return $this
	 */
	public function neueste($limit)
	{
		$this->getDbCriteria()->mergeWith(array(
			'order' => 'datum DESC',
			'limit' => $limit,
		));
		return $this;
	}



	/** @return string */
	public function getLink()
	{
		return "http://www.ris-muenchen.de" . $this->url;
	}

	/** @return string */
	public function getTypName()
	{
		return "Dokument";
	}

	/**
	 * @param bool $langfassung
	 * @return string
	 */
	public function getName($langfassung = false)
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDate() {
		return $this->datum;
	}




	/**
	 */
	public function download_and_parse()
	{
		$url      = "http://www.ris-muenchen.de" . $this->url;
		$x        = explode("/", $url);
		$filename = $x[count($x) - 1];
		if (preg_match("/[^a-zA-Z0-9_\.-]/", $filename)) die("Ungültige Zeichen im Dateinamen");

		$absolute_filename = PATH_PDF . $filename;

		RISTools::download_file($url, $absolute_filename);

		$y                   = explode(".", $filename);
		$endung              = mb_strtolower($y[count($y) - 1]);
		$this->seiten_anzahl = RISPDF2Text::document_anzahl_seiten($absolute_filename);

		if ($endung == "pdf") $this->text_pdf = RISPDF2Text::document_text_pdf($absolute_filename);
		else $this->text_pdf = "";

		$this->text_ocr_raw       = RISPDF2Text::document_text_ocr($absolute_filename, $this->seiten_anzahl);
		$this->text_ocr_corrected = RISPDF2Text::ris_ocr_clean($this->text_ocr_raw);
		$this->ocr_von            = AntragDokument::$OCR_VON_TESSERACT;

		copy($absolute_filename, OMNIPAGE_PDF_DIR . $filename);
	}

	/**
	 * @throws Exception
	 */
	public function geo_extract()
	{
		$text              = $this->text_ocr_corrected . $this->text_pdf;
		$strassen_gefunden = RISGeo::suche_strassen($text);

		/** @var array|AntragOrt[] $bisherige */
		$bisherige     = AntragOrt::model()->findAllByAttributes(array("dokument_id" => $this->id));
		$bisherige_ids = array();
		foreach ($bisherige as $i) $bisherige_ids[] = $i->ort_id;

		$neue_ids = array();
		$indexed  = array();

		foreach ($strassen_gefunden as $strasse_name) if (!in_array($strasse_name, $indexed)) {
			$indexed[] = $strasse_name;
			$geo       = OrtGeo::getOrCreate($strasse_name);
			if (is_null($geo)) continue;

			$neue_ids[] = $geo->id;

			if (!in_array($geo->id, $bisherige_ids)) {
				$antragort              = new AntragOrt();
				$antragort->antrag_id   = $this->antrag_id;
				$antragort->termin_id   = $this->termin_id;
				$antragort->dokument_id = $this->id;
				$antragort->ort_name    = $strasse_name;
				$antragort->ort_id      = $geo->id;
				$antragort->source      = "text_parse";
				$antragort->datum       = new CDbExpression("NOW()");
				if (!$antragort->save()) {
					RISTools::send_email(Yii::app()->params['adminEmail'], "AntragDokument:geo_extract Error", print_r($antragort->getErrors(), true));
					throw new Exception("Fehler beim Speichern: geo_extract");
				}
				echo "Neu angelegt: " . $antragort->ort_id . " - " . $antragort->ort_name . "\n";
			}
		}

		foreach ($bisherige_ids as $id) if (!in_array($id, $neue_ids)) {
			AntragOrt::model()->deleteAllByAttributes(array("dokument_id" => $this->id, "ort_id" => $id));
		}

		$this->orte = AntragOrt::model()->findAllByAttributes(array("dokument_id" => $this->id));
	}


	/**
	 *
	 */
	public function reDownloadIndex()
	{
		$this->download_and_parse();
		$this->save();
		$this->geo_extract();
		$this->solrIndex();
	}


	/**
	 * @param string $typ
	 * @param Antrag|Termin|AntragErgebnis $antrag_termin_ergebnis
	 * @param array $dok
	 * @throws Exception
	 * @return string
	 */
	public static function create_if_necessary($typ, $antrag_termin_ergebnis, $dok)
	{
		$x           = explode("/", $dok["url"]);
		$dokument_id = IntVal($x[count($x) - 1]);

		/** @var AntragDokument|null $dokument */
		$dokument = AntragDokument::model()->findByPk($dokument_id);
		if ($dokument) return "";

		$dokument      = new AntragDokument();
		$dokument->id  = $dokument_id;
		$dokument->typ = $typ;
		if (is_a($antrag_termin_ergebnis, "Antrag")) $dokument->antrag_id = $antrag_termin_ergebnis->id;
		if (is_a($antrag_termin_ergebnis, "Termin")) $dokument->termin_id = $antrag_termin_ergebnis->id;
		if (is_a($antrag_termin_ergebnis, "AntragErgebnis")) $dokument->ergebnis_id = $antrag_termin_ergebnis->id;
		$dokument->url   = $dok["url"];
		$dokument->name  = $dok["name"];
		$dokument->datum = new CDbExpression('NOW()');
		if (defined("NO_TEXT")) {
			throw new Exception("Noch nicht implementiert");
		} else {
			$dokument->download_and_parse();
		}

		if (!$dokument->save()) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "AntragDokument:create_if_necessary Error", print_r($dokument->getErrors(), true));
			throw new Exception("Fehler");
		}

		$dokument->geo_extract();
		$dokument->solrIndex();

		$dokument->highlightBenachrichtigung();

		return "Neue Datei: " . $dokument_id . " / " . $dok["name"] . "\n";
	}


	/**
	 * @return string
	 */
	public function getOriginalLink()
	{
		return "http://www.ris-muenchen.de" . $this->url;
	}


	private static $dokumente_cache = array();

	/**
	 * @param string $id
	 * @param bool $cached
	 * @return AntragDokument
	 */
	public static function getDocumentBySolrId($id, $cached = false)
	{
		$x  = explode(":", $id);
		$id = IntVal($x[1]);
		if ($cached) {
			if (!isset(static::$dokumente_cache[$id])) static::$dokumente_cache[$id] = AntragDokument::model()->with("antrag")->findByPk($id);
			return static::$dokumente_cache[$id];
		}
		return AntragDokument::model()->with(array("antrag", "ergebnis"))->findByPk($id);
	}

	/**
	 * @return IRISItem
	 */
	public function getRISItem()
	{
		if (in_array($this->typ, array(static::$TYP_STADTRAT_BESCHLUSS))) return $this->ergebnis;
		if (in_array($this->typ, array(static::$TYP_STADTRAT_TERMIN, static::$TYP_BA_TERMIN))) return $this->termin;
		if (in_array($this->typ, array(static::$TYP_STADTRAT_BESCHLUSS, static::$TYP_BA_BESCHLUSS))) return $this->ergebnis;
		return $this->antrag;
	}


	/**
	 * @param int $limit
	 * @return array|AntragDokument[]
	 */
	public function solrMoreLikeThis($limit = 10)
	{
		if ($GLOBALS["SOLR_CONFIG"] === null) return array();
		$solr   = RISSolrHelper::getSolrClient("ris");
		$select = $solr->createSelect();
		$select->setQuery("id:\"Document:" . $this->id . "\"");
		$select->getMoreLikeThis()->setFields("text")->setMinimumDocumentFrequency(1)->setMinimumTermFrequency(1)->setCount($limit);
		$ergebnisse = $solr->select($select);
		$mlt        = $ergebnisse->getMoreLikeThis();
		$ret        = array();
		foreach ($ergebnisse as $document) {
			$mltResult = $mlt->getResult($document->id);
			if ($mltResult) foreach ($mltResult as $mltDoc) {
				$mod = AntragDokument::model()->findByPk(str_replace("Document:", "", $mltDoc->id));
				if ($mod) $ret[] = $mod;
			}
		}
		return $ret;
	}


	/**
	 * @param Solarium\QueryType\Update\Query\Query $update
	 */
	private function solrIndex_termin_do($update)
	{
		/** @var RISSolrDocument $doc */
		$doc                     = $update->createDocument();
		$doc->id                 = "Document:" . $this->id;
		$doc->text               = RISSolrHelper::string_cleanup($this->termin->gremium->name . " " . $this->text_pdf);
		$doc->text_ocr           = RISSolrHelper::string_cleanup($this->text_ocr_corrected);
		$doc->dokument_name      = RISSolrHelper::string_cleanup($this->name);
		$doc->dokument_url       = RISSolrHelper::string_cleanup($this->url);
		$doc->antrag_wahlperiode = RISSolrHelper::string_cleanup($this->termin->wahlperiode);
		$doc->antrag_typ         = ($this->termin->ba_nr > 0 ? "ba_termin" : "stadtrat_termin");
		$doc->antrag_ba          = IntVal($this->termin->ba_nr);
		$doc->antrag_id          = $this->termin->id;
		$doc->antrag_betreff     = RISSolrHelper::string_cleanup($this->termin->gremium->name);
		$doc->termin_datum       = RISSolrHelper::mysql2solrDate($this->termin->termin);
		$max_datum               = $this->termin->termin;

		$geo            = array();
		$dokument_bas   = array();
		$dokument_bas[] = ($this->termin->ba_nr > 0 ? $this->termin->ba_nr : 0);
		foreach ($this->orte as $ort) if ($ort->ort->to_hide == 0) {
			$geo[] = $ort->ort->lat . "," . $ort->ort->lon;
			if ($ort->ort->ba_nr > 0 && !in_array($ort->ort->ba_nr, $dokument_bas)) $dokument_bas[] = $ort->ort->ba_nr;
		}
		$doc->geo          = $geo;
		$doc->dokument_bas = $dokument_bas;

		$aenderungs_datum = array();

		/** @var array|RISAenderung[] $aenderungen */
		$aenderungen = RISAenderung::model()->findAllByAttributes(array("ris_id" => $this->antrag_id), array("order" => "datum DESC"));
		foreach ($aenderungen as $o) {
			$aenderungs_datum[] = RISSolrHelper::mysql2solrDate($o->datum);
			$max_datum          = $o->datum;
		}

		$doc->aenderungs_datum = $aenderungs_datum;

		if ($max_datum != "") $doc->sort_datum = RISSolrHelper::mysql2solrDate($max_datum);

		$update->addDocuments(array($doc));

	}


	/**
	 * @param Solarium\QueryType\Update\Query\Query $update
	 */
	private function solrIndex_antrag_do($update)
	{
		if (!$this->antrag) return;

		$max_datum = "";
		/** @var RISSolrDocument $doc */
		$doc = $update->createDocument();

		$doc->id                 = "Document:" . $this->id;
		$doc->text               = RISSolrHelper::string_cleanup($this->antrag->betreff . " " . $this->text_pdf);
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

		$antrag_erstellt = $aenderungs_datum = array();
		if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $this->antrag->gestellt_am)) {
			$antrag_erstellt[]  = RISSolrHelper::mysql2solrDate($this->antrag->gestellt_am . " 12:00:00");
			$aenderungs_datum[] = RISSolrHelper::mysql2solrDate($this->antrag->gestellt_am . " 12:00:00");
			$max_datum          = $this->antrag->gestellt_am . " 12:00:00";
		}


		$geo            = array();
		$dokument_bas   = array();
		$dokument_bas[] = ($this->antrag->ba_nr > 0 ? $this->antrag->ba_nr : 0);
		foreach ($this->orte as $ort) if ($ort->ort->to_hide == 0) {
			$geo[] = $ort->ort->lat . "," . $ort->ort->lon;
			if ($ort->ort->ba_nr > 0 && !in_array($ort->ort->ba_nr, $dokument_bas)) $dokument_bas[] = $ort->ort->ba_nr;
		}
		$doc->geo          = $geo;
		$doc->dokument_bas = $dokument_bas;

		/** @var array|RISAenderung[] $aenderungen */
		$aenderungen = RISAenderung::model()->findAllByAttributes(array("ris_id" => $this->antrag_id), array("order" => "datum DESC"));
		foreach ($aenderungen as $o) {
			$aenderungs_datum[] = RISSolrHelper::mysql2solrDate($o->datum);
			$max_datum          = $o->datum;
		}

		$doc->antrag_erstellt     = $antrag_erstellt;
		$doc->aenderungs_datum    = $aenderungs_datum;
		$doc->antrag_gestellt_von = RISSolrHelper::string_cleanup($this->antrag->gestellt_von . " " . $this->antrag->initiatorInnen);


		if ($max_datum != "") $doc->sort_datum = RISSolrHelper::mysql2solrDate($max_datum);
		$update->addDocuments(array($doc));
	}


	/**
	 * @param Solarium\QueryType\Update\Query\Query $update
	 */
	private function solrIndex_beschluss_do($update)
	{
		/** @var RISSolrDocument $doc */
		$doc = $update->createDocument();

		if (is_null($this->ergebnis)) return; // Kann vorkommen, wenn ein TOP nachträglich gelöscht wurde

		$doc->id            = "Ergebnis:" . $this->id;
		$doc->text          = RISSolrHelper::string_cleanup($this->ergebnis->top_betreff . " " . $this->ergebnis->beschluss_text . " " . $this->ergebnis->entscheidung . " " . $this->text_pdf);
		$doc->text_ocr      = RISSolrHelper::string_cleanup($this->text_ocr_corrected);
		$doc->dokument_name = RISSolrHelper::string_cleanup($this->name);
		$doc->dokument_url  = $this->url;

		$geo          = array();
		$dokument_bas = array();
		foreach ($this->orte as $ort) if ($ort->ort->to_hide == 0) {
			$geo[] = $ort->ort->lat . "," . $ort->ort->lon;
			if ($ort->ort->ba_nr > 0 && !in_array($ort->ort->ba_nr, $dokument_bas)) $dokument_bas[] = $ort->ort->ba_nr;
		}
		$doc->geo          = $geo;
		$doc->dokument_bas = $dokument_bas;

		$datum           = (is_object($this->datum) ? date("Y-m-d H:i:s") : $this->datum);
		$doc->sort_datum = RISSolrHelper::mysql2solrDate($datum);
		$update->addDocuments(array($doc));
	}


	/**
	 */
	public function solrIndex()
	{

		$tries = 3;
		while ($tries > 0) try {
			$solr   = RISSolrHelper::getSolrClient("ris");
			$update = $solr->createUpdate();

			if (in_array($this->typ, array(static::$TYP_STADTRAT_TERMIN, static::$TYP_BA_TERMIN))) $this->solrIndex_termin_do($update);
			elseif (in_array($this->typ, array(static::$TYP_STADTRAT_BESCHLUSS, static::$TYP_BA_BESCHLUSS))) $this->solrIndex_beschluss_do($update);
			else $this->solrIndex_antrag_do($update);


			$update->addCommit();
			$solr->update($update);
			return;
		} catch (Exception $e) {
			$tries--;
			sleep(15);
		}
		RISTools::send_email(Yii::app()->params['adminEmail'], "Failed Indexing", print_r($this->getAttributes()));
	}

	/**
	 * @param int $limit
	 * @return AntragDokument[]
	 */
	public static function getHighlightDokumente($limit = 3)
	{
		return AntragDokument::model()->findAll(array("condition" => "highlight IS NOT NULL", "order" => "highlight DESC", "limit" => $limit));
	}

	/**
	 */
	public function highlightBenachrichtigung() {
		if ($this->seiten_anzahl >= 100) RISTools::send_email(Yii::app()->params["adminEmail"], "[RIS] Highlight?", $this->getOriginalLink());
	}

}
