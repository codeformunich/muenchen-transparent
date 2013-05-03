<?php

class RISTools {

	const STD_USER_AGENT = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17";
	const STD_PROXY = "http://127.0.0.1:8118/";


	/**
	 * @param string $text
	 * @return string
	 */
	public static function toutf8($text)
	{
		if (!function_exists('mb_detect_encoding')) {
			return $text;
		} elseif (mb_detect_encoding($text, 'UTF-8, ISO-8859-1') == "ISO-8859-1") {
			return utf8_encode($text);
		} else {
			return $text;
		}
	}

	/**
	 * @param $string
	 * @return string
	 */
	public static function bracketEscape($string) {
		return str_replace(array("[", "]"), array(urlencode("["), urlencode("]")), $string);
	}

	/**
	 * @param string $url_to_read
	 * @param string $username
	 * @param string $password
	 * @param int $timeout
	 * @return string
	 */
	public static function load_file($url_to_read, $username = "", $password = "", $timeout = 30)
	{
		$i = 0;
		do {
			$ch = curl_init();

			if ($username != "" || $password != "") curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

			curl_setopt($ch, CURLOPT_URL, $url_to_read);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_PROXY, RISTools::STD_PROXY);
			$text = curl_exec($ch);
			$text = str_replace(chr(13), "\n", $text);
			$info = curl_getinfo($ch);
			curl_close($ch);

			$text = RISTools::toutf8($text);

			if (!defined("VERYFAST")) sleep(1);
			$i++;
		} while (strpos($text, "localhost:8118") !== false && $i < 10);

		return $text;
	}

	/**
	 * @param string $url_to_read
	 * @param string $filename
	 * @param string $username
	 * @param string $password
	 * @param int $timeout
	 */
	public static function download_file($url_to_read, $filename, $username = "", $password = "", $timeout = 30)
	{
		$ch = curl_init();

		if ($username != "" || $password != "") curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

		$fp = fopen($filename, "w");
		curl_setopt($ch, CURLOPT_URL, $url_to_read);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
		//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_PROXY, RISTools::STD_PROXY);
		curl_exec($ch);
		//$info = curl_getinfo($ch);
		curl_close($ch);
		//file_put_contents($filename, $text);
		fclose($fp);

		if (!defined("VERYFAST")) sleep(1);
	}


	/**
	 * @param string $input
	 * @return int
	 */
	public static function date_iso2timestamp($input)
	{
		$x    = explode(" ", $input);
		$date = explode("-", $x[0]);

		if (count($x) == 2) $time = explode(":", $x[1]);
		else $time = array(0, 0, 0);

		return mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
	}


	/**
	 * @param string $text
	 * @return string
	 */
	public static function rssent($text)
	{
		$search  = array("<br>", "&", "\"", "<", ">", "'", "–");
		$replace = array("\n", "&amp;", "&quot;", "&lt;", "&gt;", "&apos;", "-");
		return str_replace($search, $replace, $text);
	}

	/**
	 * @param string $titel
	 * @return string
	 */
	public static function korrigiereTitelZeichen($titel) {
		$titel = preg_replace("/ \?(\\w[^\\?]*\\w)\?/siu", " „\\1“", $titel);
		$titel = preg_replace("/^\?(\\w[^\\?]*\\w)\?/siu", " „\\1“", $titel);
		$titel = preg_replace("/([0-9])\?([0-9])/siu", " \\1-\\2", $titel);
		$titel = preg_replace("/ \?$/siu", "?", $titel);
		$titel = str_replace(" ?", " —", $titel);
		return $titel;
	}


	/**
	 * @param string $str
	 * @return array
	 */
	public static function normalize_antragvon($str) {
		$a = explode(",", $str);
		$b = array();
		foreach ($a as $y) {
			$b = array_merge($b, explode("/", $y));
		}
		$ret = array();
		foreach ($b as $y) {
			$z = explode(";", $y);
			if (count($z) == 2) $y = $z[1] . " " . $z[0];
			$name_orig = $y;

			$y = mb_strtolower($y);
			$y = str_replace("herr ", "", $y);
			$y = str_replace("herrn ", "", $y);
			$y = str_replace("frau ", "", $y);
			$y = str_replace("str ", "", $y);
			$y = str_replace("str. ", "", $y);
			$y = str_replace("strin ", "", $y);
			$y = str_replace("berufsm. ", "", $y);
			$y = str_replace("dr. ", "", $y);
			$y = str_replace("prof. ", "", $y);

			$y = trim($y);

			if (mb_substr($y, 0, 3) == "ob ") $y = mb_substr($y, 3);
			if (mb_substr($y, 0, 3) == "bm ") $y = mb_substr($y, 3);

			for ($i = 0; $i < 10; $i++) $y = str_replace("  ", " ", $y);

			if (trim($y) != "") $ret[] = array("name"=>$name_orig, "name_normalized"=>$y);
		}
		return $ret;
	}


	/**
	 * @param string $name_normalized
	 * @param string $name
	 * @return Person
	 */
	public function ris_get_person_by_name($name_normalized, $name)
	{
		/** @var Person $p */
		$p = Person::model()->findByAttributes(array("name_normalized" => $name_normalized));
		if ($p) return $p;
		echo "$name / $name_normalized \n";

		$p = new Person();
		$p->name_normalized = $name_normalized;
		$p->name = $name;
		$p->typ = "sonstiges";
		$p->save();
		return $p;
	}


	/**
	 * @param string $typ
	 * @param int $ba_nr
	 * @return string
	 */
	public static function ris_get_original_name($typ, $ba_nr)
	{
		switch ($typ) {
			case "ba_antrag":
				return "BA $ba_nr Antrag";
				break;
			case "ba_initiative":
				return "BA $ba_nr Initiative";
				break;
			case "ba_termin":
				return "BA $ba_nr Termin";
				break;
			case "stadtrat_antrag":
				return "Stadtratsantrag";
				break;
			case "stadtrat_vorlage":
				return "Stadtratsvorlage";
				break;
			case "stadtrat_termin":
				return "Stadtratssitzung";
				break;
		}
		return "Unbekannt";
	}

	/**
	 * @param string $typ
	 * @param int $ba_nr
	 * @param int $id
	 * @param string $mode
	 * @return string
	 */
	public static function ris_get_original_url($typ, $ba_nr, $id, $mode = "")
	{
		switch ($typ) {
			case "ba_antrag":
				return "http://www.ris-muenchen.de/RII2/BA-RII/ba_antraege_details.jsp?Id=" . $id . "&selTyp=BA-Antrag";
				break;
			case "ba_initiative":
				return "http://www.ris-muenchen.de/RII2/BA-RII/ba_initiativen_details.jsp?Id=" . $id;
				break;
			case "ba_termin":
				return "http://www.ris-muenchen.de/RII2/BA-RII/ba_sitzungen_details.jsp?Id=" . $id;
				break;
			case "stadtrat_antrag":
				return "http://www.ris-muenchen.de/RII2/RII/ris_antrag_detail.jsp?risid=" . $id;
				break;
			case "stadtrat_vorlage":
				return "http://www.ris-muenchen.de/RII2/RII/ris_vorlagen_detail.jsp?risid=" . $id;
				break;
			case "stadtrat_termin":
				return "http://www.ris-muenchen.de/RII2/RII/ris_sitzung_detail.jsp?risid=" . $id;
				break;
		}
		return "Unbekannt";
	}
}