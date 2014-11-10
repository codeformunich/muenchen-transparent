<?php

class RISTools
{

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
	public static function bracketEscape($string)
	{
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
			//$info = curl_getinfo($ch);
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
	 * @param string $input
	 * @return string
	 */
	public static function datumstring($input)
	{
		$ts  = static::date_iso2timestamp($input);
		$tag = date("d.m.Y", $ts);
		if ($tag == date("d.m.Y")) return "Heute";
		if ($tag == date("d.m.Y", time() - 3600 * 24)) return "Gestern";
		if ($tag == date("d.m.Y", time() - 2 * 3600 * 24)) return "Vorgestern";
		return $tag;
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
	public static function korrigiereTitelZeichen($titel)
	{
		$titel = preg_replace("/([\\s-\(])\?(\\w[^\\?]*[\\w.])\?/siu", "\\1„\\2“", $titel);
		$titel = preg_replace("/^\?(\\w[^\\?]*\\w)\?/siu", " „\\1“", $titel);
		$titel = preg_replace("/([0-9])\?([0-9])/siu", " \\1-\\2", $titel);
		$titel = preg_replace("/\\s\?$/siu", "?", $titel);
		$titel = str_replace(" ?", " —", $titel);
		$titel = str_replace(chr(10) . "?", " —", $titel);
		$titel = str_replace("Â?", "€", $titel);
		return $titel;
	}


	/**
	 * @param string $str
	 * @return array
	 */
	public static function normalize_antragvon($str)
	{
		$a   = explode(",", $str);
		$ret = array();
		foreach ($a as $y) {
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
			$y = str_replace("Zeilhofer-Rath", "Zeilnhofer-Rath", $y);

			if (trim($y) != "") $ret[] = array("name" => $name_orig, "name_normalized" => $y);
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

		$p                  = new Person();
		$p->name_normalized = $name_normalized;
		$p->name            = $name;
		$p->typ             = "sonstiges";
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
				return "http://www.ris-muenchen.de/RII/BA-RII/ba_antraege_details.jsp?Id=" . $id . "&selTyp=BA-Antrag";
				break;
			case "ba_initiative":
				return "http://www.ris-muenchen.de/RII/BA-RII/ba_initiativen_details.jsp?Id=" . $id;
				break;
			case "ba_termin":
				return "http://www.ris-muenchen.de/RII/BA-RII/ba_sitzungen_details.jsp?Id=" . $id;
				break;
			case "stadtrat_antrag":
				return "http://www.ris-muenchen.de/RII/RII/ris_antrag_detail.jsp?risid=" . $id;
				break;
			case "stadtrat_vorlage":
				return "http://www.ris-muenchen.de/RII/RII/ris_vorlagen_detail.jsp?risid=" . $id;
				break;
			case "stadtrat_termin":
				return "http://www.ris-muenchen.de/RII/RII/ris_sitzung_detail.jsp?risid=" . $id;
				break;
		}
		return "Unbekannt";
	}

	/**
	 * @param string $email
	 * @param string $betreff
	 * @param string $text_plain
	 * @param null|string $text_html
	 * @param null|string $mail_tag
	 */
	public static function send_email($email, $betreff, $text_plain, $text_html = null, $mail_tag = null)
	{
		if (defined("MANDRILL_API_KEY") && strlen(MANDRILL_API_KEY) > 0) {
			static::send_email_mandrill($email, $betreff, $text_plain, $text_html, $mail_tag);
		} else {
			static::send_email_zend($email, $betreff, $text_plain, $text_html, $mail_tag);
		}
	}


	public static function send_email_mandrill($email, $betreff, $text_plain, $text_html = null, $mail_tag = null)
	{
		$mandrill = new Mandrill(MANDRILL_API_KEY);
		$tags     = array();
		if ($mail_tag !== null) $tags[] = $mail_tag;
		$message = array(
			'html'              => $text_html,
			'text'              => $text_plain,
			'subject'           => $betreff,
			'from_email'        => Yii::app()->params["adminEmail"],
			'from_name'         => Yii::app()->params["adminEmailName"],
			'to'                => array(
				array(
					"name"  => null,
					"email" => $email,
					"type"  => "to",
				)
			),
			'important'         => false,
			'tags'              => $tags,
			'track_clicks'      => false,
			'track_opens'       => false,
			'inline_css'        => true,
		);
		$mandrill->messages->send($message, false);
	}


	public static function send_email_zend($email, $betreff, $text_plain, $text_html = null, $mail_tag = null)
	{
		$mail = new Zend\Mail\Message();
		$mail->setFrom(Yii::app()->params["adminEmail"], Yii::app()->params["adminEmailName"]);
		$mail->addTo($email, $email);
		$mail->setSubject($betreff);

		$mail->setEncoding("UTF-8");

		if ($text_html !== null) {
			$converter = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles($text_html);
			$converter->setStripOriginalStyleTags(false);
			$converter->setUseInlineStylesBlock(true);
			$converter->setEncoding("UTF-8");
			$converter->setExcludeMediaQueries(true);
			$converter->setCleanup(false);
			$text_html = $converter->convert();

			$text_part          = new Zend\Mime\Part($text_plain);
			$text_part->type    = "text/plain";
			$text_part->charset = "UTF-8";
			$html_part          = new Zend\Mime\Part($text_html);
			$html_part->type    = "text/html";
			$html_part->charset = "UTF-8";
			$mimem              = new Zend\Mime\Message();
			$mimem->setParts(array($text_part, $html_part));

			$mail->setBody($mimem);
			$mail->getHeaders()->get('content-type')->setType('multipart/alternative');
		} else {
			$mail->setBody($text_plain);
			$headers = $mail->getHeaders();
			$headers->removeHeader('Content-Type');
			$headers->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
		}

		$transport = new Zend\Mail\Transport\Sendmail();
		$transport->send($mail);
	}

	/**
	 * @param string[] $arr
	 * @return string[]
	 */
	public static function makeArrValuesUnique($arr)
	{
		$val_count = array();
		foreach ($arr as $elem) {
			if (isset($val_count[$elem])) $val_count[$elem]++;
			else $val_count[$elem] = 1;
		}
		$vals_used = array();
		foreach ($arr as $i => $elem) {
			if ($val_count[$elem] == 1) continue;
			if (isset($vals_used[$elem])) $vals_used[$elem]++;
			else $vals_used[$elem] = 1;
			$arr[$i] = $elem . " (" . $vals_used[$elem] . ")";
		}
		return $arr;
	}
}
