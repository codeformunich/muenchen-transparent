<?php

class RISSucheKrits {

	/** @var array */
	public $krits = array();

	/**
	 * @param string|array $krits
	 */
	public function __construct($krits = "") {
		if ($krits !== "") {
			if (is_array($krits)) $this->krits = $krits;
			else $this->krits = json_decode($krits);
		}
	}

	/**
	 * @return string
	 */
	public function getJson() {
		return json_encode($this->krits);
	}

	/**
	 *
	 */
	public function getUrl() {
		$str = "";
		foreach ($this->krits as $krit) {
			if ($str != "") $str .= "&";
			$str .= "krit_typ[]=" . rawurlencode($krit["typ"]) . "&krit_val[]=";
			switch ($krit["typ"]) {
				case "betreff": $str .= rawurlencode($krit["suchbegriff"]); break;
				case "volltext": $str .= rawurlencode($krit["suchbegriff"]); break;
				case "antrag_typ": $str .= rawurlencode($krit["suchbegriff"]); break;
				case "antrag_wahlperiode": $str .= rawurlencode($krit["suchbegriff"]); break;
			}
		}
		return Yii::app()->createUrl("index/suche") . "/?" . $str;
	}


	/**
	 * @return RISSucheKrits
	 */
	public static function createFromUrl() {
		$x = new RISSucheKrits();
		if (isset($_REQUEST["krit_typ"])) for ($i = 0; $i < count($_REQUEST["krit_typ"]); $i++) switch ($_REQUEST["krit_typ"][$i]) {
			case "betreff": $x->addBetreffKrit($_REQUEST["krit_val"][$i]); break;
			case "volltext": $x->addVolltextsucheKrit($_REQUEST["krit_val"][$i]); break;
			case "antrag_typ": $x->addAntragTypKrit($_REQUEST["krit_val"][$i]); break;
			case "antrag_wahlperiode": $x->addWahlperiodeKrit($_REQUEST["krit_val"][$i]); break;
		}
		return $x;
	}

	/**
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 */
	public function addKritsToSolr(&$select) {
		foreach ($this->krits as $krit) switch ($krit["typ"]) {
			case "betreff":
				$helper = $select->getHelper();
				$select->createFilterQuery("betreff")->setQuery("antrag_betreff:" . $helper->escapeTerm($krit["suchbegriff"]));
				break;
			case "antrag_typ":
				$select->createFilterQuery("antrag_typ")->setQuery("antrag_typ:" . $krit["suchbegriff"]);
				break;
			case "antrag_wahlperiode":
				$select->createFilterQuery("antrag_wahlperiode")->setQuery("antrag_wahlperiode:" . $krit["suchbegriff"]);
				break;
			case "volltext":
				/** @var Solarium\QueryType\Select\Query\Component\DisMax $dismax */
				$dismax = $select->getDisMax();
				$dismax->setQueryParser('edismax');
				$dismax->setQueryFields("text text_ocr");

				$select->setQuery($krit["suchbegriff"]);
				break;
		}
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return "Erweiterte Suche";
	}


	/**
	 * @param $str
	 * @return $this
	 */
	public function addVolltextsucheKrit($str) {
		$this->krits[] = array(
			"typ" => "volltext",
			"suchbegriff" => $str
		);
		return $this;
	}

	/**
	 * @param $str
	 * @return $this
	 */
	public function addAntragTypKrit($str) {
		$this->krits[] = array(
			"typ" => "antrag_typ",
			"suchbegriff" => $str
		);
		return $this;
	}



	/**
	 * @param $str
	 * @return $this
	 */
	public function addWahlperiodeKrit($str) {
		$this->krits[] = array(
			"typ" => "antrag_wahlperiode",
			"suchbegriff" => $str
		);
		return $this;
	}

	/**
	 * @param $str
	 * @return $this
	 */
	public function addBetreffKrit($str) {
		$this->krits[] = array(
			"typ" => "betreff",
			"suchbegriff" => $str
		);
		return $this;
	}


	/**
	 * @return RISSucheKrits
	 */
	public function cloneKrits() {
		return new RISSucheKrits($this->krits);
	}


}