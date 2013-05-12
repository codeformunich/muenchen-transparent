<?php

class RISSucheKrits
{

	/** @var array */
	public $krits = array();

	/**
	 * @param string|array $krits
	 */
	public function __construct($krits = "")
	{
		if ($krits !== "") {
			if (is_array($krits)) $this->krits = $krits;
			else $this->krits = json_decode($krits);
		}
	}

	/**
	 * @return string
	 */
	public function getJson()
	{
		return json_encode($this->krits);
	}

	/**
	 * @return int
	 */
	public function getKritsCount()
	{
		return count($this->krits);
	}

	/**
	 * @return bool
	 */
	public function isGeoKrit()
	{
		foreach ($this->krits as $krit) if ($krit["typ"] == "geo") return true;
		return false;
	}

	/**
	 * @param float $lng1
	 * @param float $lat1
	 * @param float $lng2
	 * @param float $lat2
	 * @return float
	 */
	private function calcDistance($lng1, $lat1, $lng2, $lat2)
	{
		$theta  = $lng1 - $lng2;
		$dist   = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist   = acos($dist);
		$dist   = rad2deg($dist);
		$meters = $dist * 60 * 1.1515 * 1.609344 * 1000;
		return $meters;
	}

	/**
	 * @param OrtGeo $ort
	 * @return bool
	 */
	public function filterGeo($ort)
	{
		$geo_found = false;
		foreach ($this->krits as $krit) if ($krit["typ"] == "geo") {
			$geo_found = true;
			if ($ort === null) return false;
			if ($this->calcDistance($krit["lng"], $krit["lat"], $ort->lon, $ort->lat) <= $krit["radius"]) return true;
		}
		return !$geo_found;
	}

	/**
	 * @return null|array
	 */
	public function getGeoKrit() {
		foreach ($this->krits as $krit) if ($krit["typ"] == "geo") return $krit;
		return null;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public function getUrl($path = "index/suche")
	{
		$str = "";
		foreach ($this->krits as $krit) {
			if ($str != "") $str .= "&";
			$str .= "krit_typ[]=" . rawurlencode($krit["typ"]) . "&krit_val[]=";
			switch ($krit["typ"]) {
				case "betreff":
					$str .= rawurlencode($krit["suchbegriff"]);
					break;
				case "volltext":
					$str .= rawurlencode($krit["suchbegriff"]);
					break;
				case "antrag_typ":
					$str .= rawurlencode($krit["suchbegriff"]);
					break;
				case "antrag_wahlperiode":
					$str .= rawurlencode($krit["suchbegriff"]);
					break;
				case "geo":
					$str .= rawurlencode($krit["lng"] . "-" . $krit["lat"] . "-" . $krit["radius"]);
					break;
			}
		}
		return Yii::app()->createUrl($path) . "/?" . $str;
	}

	/**
	 * @return string
	 */
	public function getFeedUrl()
	{
		$krits = $this->getBenachrichtigungKrits();
		return $krits->getUrl("index/feed");
	}


	/**
	 * @return RISSucheKrits
	 */
	public static function createFromUrl()
	{
		$x = new RISSucheKrits();
		if (isset($_REQUEST["krit_typ"])) for ($i = 0; $i < count($_REQUEST["krit_typ"]); $i++) switch ($_REQUEST["krit_typ"][$i]) {
			case "betreff":
				$x->addBetreffKrit($_REQUEST["krit_val"][$i]);
				break;
			case "volltext":
				$x->addVolltextsucheKrit($_REQUEST["krit_val"][$i]);
				break;
			case "antrag_typ":
				$x->addAntragTypKrit($_REQUEST["krit_val"][$i]);
				break;
			case "antrag_wahlperiode":
				$x->addWahlperiodeKrit($_REQUEST["krit_val"][$i]);
				break;
			case "geo":
				$y = explode("-", $_REQUEST["krit_val"][$i]);
				$x->addGeoKrit($y[0], $y[1], $y[2]);
				break;
		}
		return $x;
	}

	/**
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 */
	public function addKritsToSolr(&$select)
	{
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
			case "geo":
				$helper = $select->getHelper();
				$select->createFilterQuery("geo")->setQuery($helper->geofilt("geo", $krit["lat"], $krit["lng"], ($krit["radius"] / 1000)));
				break;
		}
	}

	/**
	 * @param \Solarium\QueryType\Select\Query\Query $select
	 * @return string
	 */
	public function getSolrQueryStr($select)
	{
		foreach ($this->krits as $krit) switch ($krit["typ"]) {
			case "betreff":
				$helper = $select->getHelper();
				return "antrag_betreff:" . $helper->escapeTerm($krit["suchbegriff"]);
				break;
			case "antrag_typ":
				return "antrag_typ:" . $krit["suchbegriff"];
				break;
			case "antrag_wahlperiode":
				return "antrag_wahlperiode:" . $krit["suchbegriff"];
				break;
			case "volltext":
				return $krit["suchbegriff"];
				break;
			case "geo":
				$helper = $select->getHelper();
				return $helper->geofilt("geo", $krit["lat"], $krit["lng"], ($krit["radius"] / 1000));
				break;
		}
		return "";
	}

	/**
	 * @return RISSucheKrits
	 */
	public function getBenachrichtigungKrits()
	{
		$krits = array();
		foreach ($this->krits as $krit) if (!in_array($krit["typ"], array("antrag_wahlperiode"))) $krits[] = $krit;
		return new RISSucheKrits($krits);
	}


	/**
	 * @return string
	 */
	public function getTitle()
	{
		if (count($this->krits) == 1) switch ($this->krits[0]["typ"]) {
			case "betreff":
				return "Dokumente mit \"" . $this->krits[0]["suchbegriff"] . "\" im Betreff";
			case "antrag_typ":
				return "Dokumente des Typs \"" . $this->krits[0]["suchbegriff"] . "\"";
			case "volltext":
				return "Dokumente, die den Suchausdruck \"" . $this->krits[0]["suchbegriff"] . "\" enthalten";
			case "geo":
				$ort = OrtGeo::findClosest($this->krits[0]["lng"], $this->krits[0]["lat"]);
				return "Dokumente mit Ortsbezug (ungefähr: " . IntVal($this->krits[0]["radius"]) . "m um \"" . $ort->ort . "\")";
		}
		return json_encode($this->krits);
	}


	/**
	 * @param $str
	 * @return $this
	 */
	public function addVolltextsucheKrit($str)
	{
		$this->krits[] = array(
			"typ"         => "volltext",
			"suchbegriff" => $str
		);
		return $this;
	}

	/**
	 * @param float $lng
	 * @param float $lat
	 * @param float $radius
	 * @return $this
	 */
	public function addGeoKrit($lng, $lat, $radius)
	{
		$this->krits[] = array(
			"typ"    => "geo",
			"lng"    => FloatVal($lng),
			"lat"    => FloatVal($lat),
			"radius" => FloatVal($radius)
		);
		return $this;
	}

	/**
	 * @param $str
	 * @return $this
	 */
	public function addAntragTypKrit($str)
	{
		$this->krits[] = array(
			"typ"         => "antrag_typ",
			"suchbegriff" => $str
		);
		return $this;
	}


	/**
	 * @param $str
	 * @return $this
	 */
	public function addWahlperiodeKrit($str)
	{
		$this->krits[] = array(
			"typ"         => "antrag_wahlperiode",
			"suchbegriff" => $str
		);
		return $this;
	}

	/**
	 * @param $str
	 * @return $this
	 */
	public function addBetreffKrit($str)
	{
		$this->krits[] = array(
			"typ"         => "betreff",
			"suchbegriff" => $str
		);
		return $this;
	}


	/**
	 * @return RISSucheKrits
	 */
	public function cloneKrits()
	{
		return new RISSucheKrits($this->krits);
	}


}