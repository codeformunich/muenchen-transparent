<?php

/**
 * This is the model class for table "antraege_dokumente".
 *
 * The followings are the available columns in table 'antraege_dokumente':
 * @property integer $id
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
 * @property integer $seiten_anzahl
 *
 * The followings are the available model relations:
 * @property Antrag $antrag
 * @property Termin $termin
 * @property AntragErgebnis $ergebnis
 * @property AntragOrt[] $orte
 */
class AntragDokument extends CActiveRecord
{

	public static $TYP_STADTRAT_ANTRAG = "stadtrat_antrag";
	public static $TYP_STADTRAT_VORLAGE = "stadtrat_vorlage";
	public static $TYP_STADTRAT_TERMIN = "stadtrat_termin";
	public static $TYP_STADTRAT_BESCHLUSS = "stadtrat_beschluss";
	public static $TYP_BA_ANTRAG = "ba_antrag";
	public static $TYP_BA_INITIATIVE = "ba_initiative";
	public static $TYP_BA_TERMIN = "ba_termin";
	public static $TYPEN_ALLE = array(
		"stadtrat_antrag"    => "Stadtratsantrag",
		"stadtrat_vorlage"   => "Stadtratsvorlage",
		"stadtrat_termin"    => "Stadtrat: Termin",
		"stadtrat_beschluss" => "Stadtratsbeschluss",
		"ba_antrag"          => "BA: Antrag",
		"ba_initiative"      => "BA: Initiative",
		"ba_termin"          => "BA: Termin",
	);

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
			array('id, antrag_id, termin_id, ergebnis_id, seiten_anzahl', 'numerical', 'integerOnly' => true),
			array('typ', 'length', 'max' => 25),
			array('url', 'length', 'max' => 500),
			array('name', 'length', 'max' => 200),
			array('text_ocr_raw, text_ocr_corrected, text_ocr_garbage_seiten, text_pdf', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, typ, antrag_id, termin_id, ergebnis_id, url, name, datum, text_ocr_raw, text_ocr_corrected, text_ocr_garbage_seiten, text_pdf', 'safe', 'on' => 'search'),
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
			'antrag'   => array(self::BELONGS_TO, 'Antrag', 'antrag_id'),
			'termin'   => array(self::BELONGS_TO, 'Termin', 'termin_id'),
			'ergebnis' => array(self::BELONGS_TO, 'AntragErgebnis', 'ergebnis_id'),
			'orte'     => array(self::HAS_MANY, 'AntragOrt', 'dokument_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                      => 'ID',
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
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('typ', $this->typ, true);
		$criteria->compare('antrag_id', $this->antrag_id);
		$criteria->compare('termin_id', $this->termin_id);
		$criteria->compare('ergebnis_id', $this->ergebnis_id);
		$criteria->compare('url', $this->url, true);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('datum', $this->datum, true);
		$criteria->compare('text_ocr_raw', $this->text_ocr_raw, true);
		$criteria->compare('text_ocr_corrected', $this->text_ocr_corrected, true);
		$criteria->compare('text_ocr_garbage_seiten', $this->text_ocr_garbage_seiten, true);
		$criteria->compare('text_pdf', $this->text_pdf, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
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


	/**
	 */
	public function download_and_parse()
	{
		$url      = "http://www.ris-muenchen.de" . $this->url;
		$x        = explode("/", $url);
		$filename = $x[count($x) - 1];
		if (preg_match("/[^a-zA-Z0-9_\.-]/", $filename)) die("Ungültige Zeichen im Dateinamen");

		$absolute_filename = PDF_PDF . $filename;

		RISTools::download_file($url, $absolute_filename);

		$y      = explode(".", $filename);
		$endung = mb_strtolower($y[count($y) - 1]);
		$this->seiten_anzahl = RISPDF2Text::document_anzahl_seiten($absolute_filename);

		if ($endung == "pdf") $this->text_pdf = RISPDF2Text::document_text_pdf($absolute_filename);
		else $this->text_pdf = "";

		$this->text_ocr_raw       = RISPDF2Text::document_text_ocr($absolute_filename, $this->seiten_anzahl);
		$this->text_ocr_corrected = RISPDF2Text::ris_ocr_clean($this->text_ocr_raw);
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
					if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "AntragDokument:geo_extract Error", print_r($antragort->getErrors(), true));
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
			if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "AntragDokument:create_if_necessary Error", print_r($dokument->getErrors(), true));
			throw new Exception("Fehler");
		}

		$dokument->geo_extract();
		$dokument->solrIndex();

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
	public static function getDocumentBySolrId($id, $cached = false) {
		$x = explode(":", $id);
		$id = IntVal($x[1]);
		if ($cached) {
			if (!isset(static::$dokumente_cache[$id])) static::$dokumente_cache[$id] = AntragDokument::model()->with("antrag")->findByPk($id);
			return static::$dokumente_cache[$id];
		}
		return AntragDokument::model()->with("antrag")->findByPk($id);
	}

	/**
	 * @return IRISItem
	 */
	public function getRISItem() {
		if (in_array($this->typ, array(static::$TYP_STADTRAT_TERMIN, static::$TYP_BA_TERMIN))) return $this->termin;
		else return $this->antrag;
	}


	/**
	 * @param int $limit
	 * @return array|AntragDokument[]
	 */
	public function solrMoreLikeThis($limit = 10) {
		$solr   = RISSolrHelper::getSolrClient("ris");
		$select = $solr->createSelect();
		$select->setQuery("id:\"Document:" . $this->id . "\"");
		$select->getMoreLikeThis()->setFields("text")->setMinimumDocumentFrequency(1)->setMinimumTermFrequency(1) /* ->setCount(10) */;
		$ergebnisse = $solr->select($select);
		$mlt = $ergebnisse->getMoreLikeThis();
		$ret = array();
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
		$max_datum = "";
		$doc       = $update->createDocument();

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

		$antrag_erstellt = $aenderungs_datum = array();
		if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $this->antrag->gestellt_am)) {
			$antrag_erstellt[]  = RISSolrHelper::mysql2solrDate($this->antrag->gestellt_am . " 12:00:00");
			$aenderungs_datum[] = RISSolrHelper::mysql2solrDate($this->antrag->gestellt_am . " 12:00:00");
			$max_datum          = $this->antrag->gestellt_am . " 12:00:00";
		}

		/** @var array|RISAenderung[] $aenderungen */
		$aenderungen = RISAenderung::model()->findAllByAttributes(array("ris_id" => $this->antrag_id), array("order" => "datum DESC"));
		foreach ($aenderungen as $o) {
			$aenderungs_datum[] = RISSolrHelper::mysql2solrDate($o->datum);
			$max_datum          = $o->datum;
		}

		$doc->antrag_erstellt     = $antrag_erstellt;
		$doc->aenderungs_datum    = $aenderungs_datum;
		$doc->antrag_gestellt_von = RISSolrHelper::string_cleanup($this->antrag->gestellt_von . " " . $this->antrag->initiatorInnen);

		$geo = array();
		foreach ($this->orte as $ort) if ($ort->ort->to_hide == 0) {
			$geo[] = $ort->ort->lat . "," . $ort->ort->lon;
		}
		$doc->geo = $geo;

		if ($max_datum != "") $doc->sort_datum = RISSolrHelper::mysql2solrDate($max_datum);
		$update->addDocuments(array($doc));
	}


	/**
	 * @param Solarium\QueryType\Update\Query\Query $update
	 */
	private function solrIndex_beschluss_do($update)
	{
		$doc = $update->createDocument();

		if (is_null($this->ergebnis)) {
			if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "AntragDokument:solrIndex_beschluss_do Error", print_r($this, true));
			return;
		}

		$doc->id            = "Ergebnis:" . $this->id;
		$doc->text          = RISSolrHelper::string_cleanup($this->ergebnis->top_betreff . " " . $this->ergebnis->beschluss_text . " " . $this->ergebnis->entscheidung . " " . $this->text_pdf);
		$doc->text_ocr      = RISSolrHelper::string_cleanup($this->text_ocr_corrected);
		$doc->dokument_name = RISSolrHelper::string_cleanup($this->name);
		$doc->dokument_url  = $this->url;

		$datum           = (is_object($this->datum) ? date("Y-m-d H:i:s") : $this->datum);
		$doc->sort_datum = RISSolrHelper::mysql2solrDate($datum);
		$update->addDocuments(array($doc));
	}


	/**
	 */
	public function solrIndex()
	{

		$solr   = RISSolrHelper::getSolrClient("ris");
		$update = $solr->createUpdate();

		if (in_array($this->typ, array(static::$TYP_STADTRAT_TERMIN, static::$TYP_BA_TERMIN))) $this->solrIndex_termin_do($update);
		elseif (in_array($this->typ, array(static::$TYP_STADTRAT_BESCHLUSS))) $this->solrIndex_beschluss_do($update); else $this->solrIndex_antrag_do($update);


		$update->addCommit();
		$solr->update($update);
	}

}
