<?php

class RISGeo
{

	public static $STREETS_INITIALIZED = false;

	/** @var null|array|Strasse[] */
	public static $STREETS = null;

	public static $RIS_STREET_IGNORE_STREETS = array(
		"tal",
	);

	public static function get_RIS_STREET_CLEAN_REPLACES_1()
	{
		return array(
			"."              => "",
			"-"              => "",
			"ö"              => "oe",
			"ü"              => "ue",
			"ä"              => "ae",
			"ß"              => "ss",
			utf8_decode("ö") => "oe",
			utf8_decode("ä") => "ae",
			utf8_decode("ü") => "ue",
			utf8_decode("ß") => "ss",
			" \n"            => " ",
			"\n "            => " ",
			"\n"             => " ",
			"  "             => " ",
		);
	}

	public static function get_RIS_STREET_CLEAN_REPLACES_2()
	{
		return array(
			"e ost"        => "e#ost",
			"ende weg"     => "ende#weg",
			"ortskern str" => "ortskern#str",
			"frauenhofer"  => "fraunhofer",
		);
	}

	public static function addressToGeo($land, $plz, $ort, $strasse)
	{
		return ris_intern_address2geo($land, $plz, $ort, $strasse);
	}

	public static function init_streets()
	{
		if (static::$STREETS_INITIALIZED) return;
		$streets_by_length = array();
		$streets_by_norm   = array();
		$maxlength         = 0;

		/** @var array|Strasse[] $strassen */
		$strassen = Strasse::model()->findAll();
		foreach ($strassen as $strasse) {
			$norm                     = static::ris_street_cleanstring($strasse->name);
			$strasse->name_normalized = $norm;
			$streets_by_norm[$norm]   = $strasse;
			$l                        = mb_strlen($norm);
			if (!isset($streets_by_length[$l])) $streets_by_length[$l] = array();
			$streets_by_length[$l][] = $norm;
			if ($l > $maxlength) $maxlength = $l;
		}
		static::$STREETS = array();
		for ($i = $maxlength; $i > 0; $i--) if (isset($streets_by_length[$i])) foreach ($streets_by_length[$i] as $n) {
			if (!in_array($n, static::$RIS_STREET_IGNORE_STREETS)) static::$STREETS[] = $streets_by_norm[$n];
		}
		static::$STREETS_INITIALIZED = true;
	}

	public static function ris_street_cleanstring($name)
	{
		$name = str_replace("\r", "\n", $name);
		$name = preg_replace("/[0-9]+[,\\. ]*[0-9][,\\. 0-9]*E+/s", "", $name); // greedy!
		$name = preg_replace("/\n+ *[^a-z]+/si", "\n", $name); // greedy!

		$name = strtolower($name);

		$froms                       = $tos = array();
		$RIS_STREET_CLEAN_REPLACES_1 = static::get_RIS_STREET_CLEAN_REPLACES_1();
		foreach ($RIS_STREET_CLEAN_REPLACES_1 as $key => $val) {
			$froms[] = $key;
			$tos[]   = $val;
		}
		$name = str_replace($froms, $tos, $name);

		$name = str_replace("strasse", "str", $name);
		$name = preg_replace("/([0-9]) +([0-9])/", "\\1#\\2", $name);
		$name = preg_replace("/[\n\r]+([0-9])/", "#\\2", $name);
		$name = preg_replace("/[0-9]+[,\\.][0-9]+/", "", $name);

		$froms                       = $tos = array();
		$RIS_STREET_CLEAN_REPLACES_2 = static::get_RIS_STREET_CLEAN_REPLACES_2();
		foreach ($RIS_STREET_CLEAN_REPLACES_2 as $key => $val) {
			$froms[] = $key;
			$tos[]   = $val;
		}
		$name = str_replace($froms, $tos, $name);

		$name = str_replace(" ", "", $name);

		return $name;
	}


	public static function suche_strassen($str)
	{
		$fp = fopen("/tmp/strassen.log", "a"); fwrite($fp, "START\n==========\n" . $str . "\n\n\n"); fclose($fp);
		static::init_streets();
		$antragtext    = static::ris_street_cleanstring($str);
		$streets_found = array();
		foreach (static::$STREETS as $street) {
			$fp = fopen("/tmp/strassen.log", "a"); fwrite($fp, "Str: " . $street->name_normalized . "\n"); fclose($fp);
			$offset = -1;
			while (($offset = mb_strpos($antragtext, $street->name_normalized, $offset + 1)) !== false) {
				$fp = fopen("/tmp/strassen.log", "a"); fwrite($fp, "- $offset\n"); fclose($fp);
				$danach = mb_substr($antragtext, $offset + mb_strlen($street->name_normalized), 10);
				$nr     = IntVal($danach);
				$ort    = ($nr > 0 && $nr < 1000 ? $street->name . " $nr" : $street->name);

				$falsepositive = false;
				if (mb_substr($antragtext, $offset - 11, 11) == "haltestelle") $falsepositive = true;
				if ($street->name_normalized == "richardstr" && mb_substr($danach, 0, 2) == "au") $falsepositive = true;

				if (!$falsepositive && !in_array($ort, $streets_found)) {
					$streets_found[] = RISTools::toutf8($ort);
				}
				for ($i = $offset; $i < $offset + mb_strlen($street->name_normalized); $i++) $antragtext[$i] = "#";
			}
		}

		$fp = fopen("/tmp/strassen.log", "a"); fwrite($fp, "Str Fertig 2\n"); fclose($fp);

		$streets_found_consolidated = array();
		foreach ($streets_found as $street) {
			if (preg_match("/[0-9]/siu", $street)) $streets_found_consolidated[] = $street;
			else {
				$mit_hausnummer_gefunden = false;
				foreach ($streets_found as $street2) if (mb_strpos($street2, $street . " ") === 0 && preg_match("/[0-9]/siu", $street2)) $mit_hausnummer_gefunden = true;
				if (!$mit_hausnummer_gefunden) $streets_found_consolidated[] = $street;
			}
		}

		$fp = fopen("/tmp/strassen.log", "a"); fwrite($fp, "Str ferti 2\n"); fclose($fp);

		return $streets_found_consolidated;
	}


	public static function getDistanceFormula($lon, $lat, $feldl = "laenge", $feldb = "breite")
	{
		$lrad    = deg2rad($lon);
		$brad    = deg2rad($lat);
		$formula = 'IFNULL(';
		$formula .= "(ACOS((SIN($brad)*SIN(RADIANS($feldb))) + ";
		$formula .= "(COS($brad)*COS(RADIANS($feldb))*COS(RADIANS($feldl)-$lrad))) * 6371)";
		$formula .= ',0)';
		return $formula;
	}

}