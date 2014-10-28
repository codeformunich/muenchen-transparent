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
	public function getGeoKrit()
	{
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
				case "ba":
					$str .= IntVal($krit["ba_nr"]);
					break;
				case "geo":
					$str .= rawurlencode($krit["lng"] . "-" . $krit["lat"] . "-" . $krit["radius"]);
					break;
				case "referat":
					$str .= $krit["referat_id"];
					break;
				case "antrag_nr":
					$str .= rawurlencode($krit["suchbegriff"]);
					break;
			}
		}
		return Yii::app()->createUrl($path) . "/?" . $str;
	}

	/**
	 * @return array
	 */
	public function getUrlArray()
	{
		$krits = $vals = array();
		foreach ($this->krits as $krit) {
			$krits[] = $krit["typ"];
			if ($krit["typ"] == "geo") $vals[] = $krit["lng"] . "-" . $krit["lat"] . "-" . $krit["radius"];
			elseif ($krit["typ"] == "ba") $vals[] = $krit["ba_nr"];
			elseif ($krit["typ"] == "referat") $vals[] = $krit["referat_id"];
			else $vals[] = $krit["suchbegriff"];
		}
		return array("krit_typ" => $krits, "krit_val" => $vals);
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
	 * @param array $request
	 * @return RISSucheKrits
	 */
	public static function createFromUrl($request)
	{
		$x = new RISSucheKrits();
		if (isset($request["krit_typ"])) for ($i = 0; $i < count($request["krit_typ"]); $i++) switch ($request["krit_typ"][$i]) {
			case "betreff":
				$x->addBetreffKrit($request["krit_val"][$i]);
				break;
			case "volltext":
				$x->addVolltextsucheKrit($request["krit_val"][$i]);
				break;
			case "antrag_typ":
				$x->addAntragTypKrit($request["krit_val"][$i]);
				break;
			case "antrag_wahlperiode":
				$x->addWahlperiodeKrit($request["krit_val"][$i]);
				break;
			case "ba":
				$x->addBAKrit($request["krit_val"][$i]);
				break;
			case "geo":
				$y = explode("-", $request["krit_val"][$i]);
				$x->addGeoKrit($y[0], $y[1], $y[2]);
				break;
			case "referat":
				$x->addReferatKrit($request["krit_val"][$i]);
				break;
			case "antrag_nr":
				$x->addAntragNrKrit($request["krit_val"][$i]);
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
			case "ba":
				$select->createFilterQuery("dokument_bas")->setQuery("dokument_bas:" . $krit["ba_nr"]);
				break;
			case "geo":
				$helper = $select->getHelper();
				$select->createFilterQuery("geo")->setQuery($helper->geofilt("geo", $krit["lat"], $krit["lng"], ($krit["radius"] / 1000)));
				break;
			case "referat":
				$helper = $select->getHelper();
				$select->createFilterQuery("referat")->setQuery("referat_id:" . $helper->escapeTerm($krit["referat_id"]));
				break;
			case "antrag_nr":
				/** @var Solarium\QueryType\Select\Query\Component\DisMax $dismax */
				$dismax = $select->getDisMax();
				$dismax->setQueryParser('edismax');
				$dismax->setQueryFields("antrag_nr");
				$select->setQuery("*" . $krit["suchbegriff"] . "*");
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
			case "ba":
				return "ba:" . $krit["ba_nr"];
				break;
			case "volltext":
				return $krit["suchbegriff"];
				break;
			case "geo":
				$helper = $select->getHelper();
				return $helper->geofilt("geo", $krit["lat"], $krit["lng"], ($krit["radius"] / 1000));
				break;
			case "referat":
				return "referat_id:" . $krit["referat_id"];
				break;
			case "antrag_nr":
				return "*" . $krit["antrag_nr"] . "*";
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
				return "Dokumente des Typs \"" . AntragDokument::$TYPEN_ALLE[$this->krits[0]["suchbegriff"]] . "\"";
			case "volltext":
				return "Dokumente, die den Suchausdruck \"" . $this->krits[0]["suchbegriff"] . "\" enthalten";
			case "ba":
				/** @var Bezirksausschuss $ba */
				$ba = Bezirksausschuss::model()->findByAttributes(array("ba_nr" => $this->krits[0]["ba_nr"]));
				return "Bezirksausschuss " . $ba->ba_nr . ": " . $ba->name;
			case "geo":
				$ort = OrtGeo::findClosest($this->krits[0]["lng"], $this->krits[0]["lat"]);
				return "Dokumente mit Ortsbezug (ungefähr: " . IntVal($this->krits[0]["radius"]) . "m um \"" . $ort->ort . "\")";
			case "referat":
				/** @var Referat $ref */
				$ref = Referat::model()->findByPk($this->krits[0]["referat_id"]);
				return $ref->name;
				break;
			case "antrag_nr":
				return "Antrag Nr. " . str_replace("*", " ", $this->krits[0]["suchbegriff"]);
		}
		if (count($this->krits) > 1) {
			$krits = array();
			foreach ($this->krits as $cr) switch ($cr["typ"]) {
				case "betreff":
					$krits[] = "mit \"" . $cr["suchbegriff"] . "\" im Betreff";
					break;
				case "antrag_typ":
					$krits[] = "vom Typ \"" . AntragDokument::$TYPEN_ALLE[$cr["suchbegriff"]] . "\"";
					break;
				case "volltext":
					$krits[] = "mit dem Suchausdruck \"" . $cr["suchbegriff"] . "\"";
					break;
				case "ba":
					/** @var Bezirksausschuss $ba */
					$ba      = Bezirksausschuss::model()->findByAttributes(array("ba_nr" => $cr["ba_nr"]));
					$krits[] = "aus dem Bezirksausschuss " . $ba->ba_nr . ": " . $ba->name;
					break;
				case "geo":
					$ort     = OrtGeo::findClosest($this->krits[0]["lng"], $cr["lat"]);
					$krits[] = "mit einem Ortsbezug (ungefähr: " . IntVal($cr["radius"]) . "m um \"" . $ort->ort . "\")";
					break;
				case "antrag_nr":
					$krits[] = "zum Antrag Nr. " . $cr["suchbegriff"];
					break;
				case "referat":
					/** @var Referat $ref */
					$ref = Referat::model()->findByPk($this->krits[0]["referat_id"]);
					$krits[] = "im Zuständigkeitsbereich des " . $ref->name;
					break;
				default:
					$krits[] = json_encode($cr);
			}
			$text = "Dokumente ";
			for ($i = 0; $i < (count($krits) - 1); $i++) {
				$text .= $krits[$i];
				if ($i < (count($krits) - 2)) $text .= ", ";
			}
			$text .= " und " . $krits[count($krits) - 1];
			return $text;
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
	 * @param int $ba_nr
	 * @return $this
	 */
	public function addBAKrit($ba_nr)
	{
		$this->krits[] = array(
			"typ"   => "ba",
			"ba_nr" => IntVal($ba_nr)
		);
		return $this;
	}

	public function addReferatKrit($referat_id)
	{
		$this->krits[] = array(
			"typ"        => "referat",
			"referat_id" => IntVal($referat_id)
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
	 * @param string $str
	 * @return $this
	 */
	public function addAntragNrKrit($str)
	{
		$str           = preg_replace("/[^a-zA-Z0-9 \/-]/siu", "", $str);
		$str           = preg_replace("/ +/siu", "*", $str);
		$this->krits[] = array(
			"typ"         => "antrag_nr",
			"suchbegriff" => $str,
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