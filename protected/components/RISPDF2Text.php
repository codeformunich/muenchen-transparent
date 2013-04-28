<?php

class RISPDF2Text
{

	public static $RIS_OCR_CLEAN_I_REPLACES = array(
		"ı"           => "i",
		"vv"          => "w",
		"Munchen"     => "München",
		"Offentliche" => "Öffentliche",
		" fur "       => " für ",
		"reterat"     => "refereat",
		"A/t"         => "alt",
		"l\/l"        => "M",
		"\/\/"        => "W",
		"\/Wrt"       => "Wirt",
		"\/"          => "v",
		"i<"          => "k",
		"vorIage"     => "vorlage",
		"ßeamt"       => "Beamt",
		"u ße"        => "uße",
		"e ße"        => "eße",
		" dle "       => " die ",
		" lsar"       => " Isar",
		"Lander"      => "Länder",
		"Anderung"    => "Änderung",
		"vWWv"        => "www",
		"Vlﬁ"         => "Wi",
		"Scn"         => "sch",
	);

	public static $RIS_OCR_CLEAN_REPLACES = array(
		"nıv"     => "rw",
		" lsar"   => " Isar",
		"-lsar"   => "-Isar",
		"RlN"     => "RIN",
		"gen/ers" => "gervers",
		"Sen/er"  => "Server",
		"chen/er" => "cherver",
		"äahr"    => "Jahr",
		" ln"     => " In",
		" lm"     => " Im",
		" iange"  => " lange",
		"Grnbi-i" => "GmbH",
		"SWMIMVG" => "SWM/MVG",
		"MVGISWM" => "MVG/SWM",
		"tiich"   => "tlich",
		"Schuie"  => "Schule",
	);

	public static $RIS_OCR_CLEAN_PREG_REPLACES = array(
		"/^l([bcdfghkmnpqrstvwxz])/um" => "I\\1",
		"/ l([bcdfghkmnpqrstvwxz])/um" => " I\\1",
		"/ i([aeou])/um"               => " l\\1",
		"/(niv)(?!eau)/u"              => "rw",
	);

	/**
	 * @param string $filename
	 * @return int
	 */
	public static function document_anzahl_seiten($filename)
	{
		$result = array();
		exec(PATH_PDFINFO . " $filename", $result);
		preg_match("/Pages:\\s*([0-9]+)/siu", implode("", $result), $matches);
		return (isset($matches[1]) ? IntVal($matches[1]) : 0);
	}

	/**
	 * @param string $filename
	 * @param int $seiten_anzahl
	 * @return string
	 */
	public static function document_text_ocr($filename, $seiten_anzahl)
	{
		$depth = "-depth 8";
		$text = "";
		for ($i = 0; $i < $seiten_anzahl; $i++) {
			$tif_tmp_file = TMP_PATH . "ocr-tmp." . rand(0, 1000000000) . ".tif";
			exec(PATH_CONVERT . " -density 300x300 \"${filename}[$i]\" -colorspace Gray $depth $tif_tmp_file", $result);
			if (file_exists($tif_tmp_file)) {
				exec(PATH_TESSERACT . " $tif_tmp_file $tif_tmp_file -l deu -psm 1", $result);
				$text .= "########## SEITE " . ($i + 1) . " ##########\n";
				if (file_exists($tif_tmp_file . ".txt")) {
					$text .= file_get_contents($tif_tmp_file . ".txt");
					unlink($tif_tmp_file . ".txt");
				};
				unlink($tif_tmp_file);
			}
		}
		return $text;
	}

	public static function document_text_pdf($pdf)
	{
		$tmp_file_name = TMP_PATH . "pdf.txt." . rand(0, 10000000);
		exec(PATH_PDFTOTEXT . " -enc UTF-8 $pdf $tmp_file_name", $ret);
		if (file_exists($tmp_file_name)) {
			$text = file_get_contents($tmp_file_name);
			unlink($tmp_file_name);
		} else {
			if (Yii::app()->params['adminEmail'] != "") mail(Yii::app()->params['adminEmail'], "PDFParse Error", $pdf . "\n" . print_r($ret, true));
			$text = "";
		}
		return $text;
	}


	public static function ris_ocr_clean($txt)
	{
		$ord_a = ord("a");
		$ord_z = ord("z");

		$txt = str_replace(" \n", "\n", $txt);
		$txt = str_replace("-\n", "", $txt);
		foreach (static::$RIS_OCR_CLEAN_I_REPLACES as $from => $to) $txt = str_ireplace($from, $to, $txt);
		foreach (static::$RIS_OCR_CLEAN_REPLACES as $from => $to) $txt = str_replace($from, $to, $txt);
		foreach (static::$RIS_OCR_CLEAN_PREG_REPLACES as $from => $to) $txt = preg_replace($from, $to, $txt);

		$first = -1;
		while ($first = strpos($txt, "I", $first + 1)) {
			if ($first == 0) continue;
			$c = ord($txt[$first - 1]);
			if ($c >= $ord_a && $c <= $ord_z) $txt[$first] = "l";
		}

		if (substr($txt, 0, 3) == "ll.") {
			$txt[0] = "I";
			$txt[1] = "I";
		}
		if (substr($txt, 0, 4) == "lll.") {
			$txt[0] = "I";
			$txt[1] = "I";
			$txt[2] = "I";
		}
		return $txt;
	}
}