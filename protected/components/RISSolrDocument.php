<?php
 class RISSolrDocument implements  Solarium\QueryType\Update\Query\Document\DocumentInterface {

	 /** @var int */
	 public $id;
	 public $antrag_ba;
	 public $antrag_id;
	 public $referat_id;

	 /** @var string */
	 public $text;
	 public $text_ocr;
	 public $dokument_name;
	 public $dokument_url;
	 public $antrag_nr;
	 public $antrag_wahlperiode;
	 public $antrag_typ;
	 public $antrag_betreff;
	 public $antrag_erstellt;
	 public $aenderungs_datum;
	 public $antrag_gestellt_von;
	 public $sort_datum;
	 public $termin_datum;

	 /** @var array */
	 public $geo;

	 /** @var int[] */
	 public $dokument_bas;

	 /**
	  * @param array $fields
	  * @param array $boosts
	  * @param array $modifiers
	  */
	 public function __construct(array $fields = array(), array $boosts = array(), array $modifiers = array())
	 {
	 }
 }