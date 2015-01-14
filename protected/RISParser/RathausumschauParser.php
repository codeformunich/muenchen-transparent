<?php

class RathausumschauParser extends RISParser
{

	public function parse($id)
	{
		/** @var Rathausumschau $ru */
		$ru = Rathausumschau::model()->findByPk($id);

		if (count($ru->dokumente) > 0 && $ru->dokumente[0]->text_pdf != "") return;
		if (count($ru->dokumente) > 0) {
			if ($ru->dokumente[0]->text_pdf != "") return;
			$dokument = $ru->dokumente[0];
		} else {
			$result = Yii::app()->db->createCommand("SELECT MIN(id) minid FROM dokumente")->queryAll();
			$id     = $result[0]["minid"];
			if ($id >= 0) $id = 0;
			$id--;
			$dokument                    = new Dokument();
			$dokument->id                = $id;
			$dokument->typ               = Dokument::$TYP_RATHAUSUMSCHAU;
			$dokument->rathausumschau_id = $ru->id;
		}

		$dokument->url            = $ru->url;
		$dokument->datum          = $ru->datum;
		$dokument->datum_dokument = $ru->datum;
		$dokument->name           = "Rathausumschau " . $ru->nr . "/" . substr($ru->datum, 0, 4);
		$dokument->text_pdf       = "";
		if (!$dokument->save()) {
			var_dump($dokument->getErrors());
			die();
		}

		$dokument->reDownloadIndex();

		echo "Load: " . $ru->url . "\n";
	}

	public function parseSeite($seite, $first)
	{

	}

	public function parseAlle()
	{
		$this->parseArchive3(date("Y"));
	}


	/* 2012, 2013, 2014 */
	public function parseArchive3($jahr)
	{
		$url = ris_download_string("http://www.muenchen.de/rathaus/Stadtinfos/Presse-Service/" . $jahr . ".html");
		if ($jahr == 2012) $url .= ris_download_string("http://www.muenchen.de/rathaus/Stadtinfos/Presse-Service/Presse-Archiv/2012/2012--Jan-bis-Juni.html");
		if ($jahr == 2013) $url .= ris_download_string("http://www.muenchen.de/rathaus/Stadtinfos/Presse-Service/Presse-Archiv/2013/2013--Jan-bis-Juni.html");
		if ($jahr == 2014) $url = ris_download_string("http://www.muenchen.de/rathaus/Stadtinfos/Presse-Service/Presse-Archiv/" . $jahr . ".html");
		//preg_match_all("/Rathaus Umschau (?<nr>[0-9]+) vom (?<datum>[0-9\.]+)&nbsp[^<]+<a href=\"(?<url>[^\"]+)\"/siu", $url, $matches);
		preg_match_all("/<a href=\"(?<url>[^\"]+\.pdf)\"[^>]*>(Rathaus Umschau )?(?<nr>[0-9]+)[^0-9].+vom (?<datum>[0-9\.]+)&/siuU", $url, $matches);

		for ($i = 0; $i < count($matches["url"]); $i++) {
			$datum = explode(".", $matches["datum"][$i]);
			$ru    = Rathausumschau::model()->findByAttributes(array("jahr" => $datum[2], "nr" => $matches["nr"][$i]));
			if (!$ru) {
				$ru        = new Rathausumschau();
				$ru->nr    = $matches["nr"][$i];
				$ru->url   = $matches["url"][$i];
				$ru->jahr  = $datum[2];
				$ru->datum = $datum[2] . "-" . $datum[1] . "-" . $datum[0];
				$ru->save();
			}
			$this->parse($ru->id);
		}
	}


	public static $MON_MAPPING = array(
		"Jan"  => 1,
		"Feb"  => 2,
		"Mrz"  => 3,
		"Apr"  => 4,
		"Mai"  => 5,
		"Jun"  => 6,
		"Jul"  => 7,
		"Aug"  => 8,
		"Sept" => 9,
		"Sep"  => 9,
		"Okt"  => 10,
		"Nov"  => 11,
		"Dez"  => 12,
	);

	/* 2009, 2010, 2011 */
	public function parseArchive2($jahr)
	{
		$url  = "http://www.muenchen.info/pia/Archiv/RathausUmschauArchiv/$jahr/";
		$site = ris_download_string($url);
		$site = utf8_encode($site);

		preg_match_all("/Rathaus Umschau (?<nr>[0-9]+)[abc]?\.pdf vom (?<tag>[0-9]+)\. (?<mon>[a-z]+)\./siu", $site, $matches);

		for ($i = 0; $i < count($matches["nr"]); $i++) {
			$datum = $jahr . "-" . static::$MON_MAPPING[$matches["mon"][$i]] . "-" . $matches["tag"][$i];

			$ru = Rathausumschau::model()->findByAttributes(array("jahr" => $jahr, "nr" => IntVal($matches["nr"][$i])));
			if (!$ru) {
				$ru        = new Rathausumschau();
				$ru->nr    = IntVal($matches["nr"][$i]);
				$ru->url   = $url . $matches["nr"][$i] . ".pdf";
				$ru->jahr  = $jahr;
				$ru->datum = $datum;
				$ru->save();
			}
			$this->parse($ru->id);
		}
	}

	public static $MONAT_MAPPING = array(
		"Januar"    => 1,
		"Februar"   => 2,
		"März"      => 3,
		"April"     => 4,
		"Mai"       => 5,
		"Juni"      => 6,
		"Juli"      => 7,
		"August"    => 8,
		"September" => 9,
		"Oktober"   => 10,
		"November"  => 11,
		"Dezember"  => 12,
	);

	/* 2005, 2006, 2007, 2008 */
	public function parseArchive1($jahr)
	{
		$dir = PATH_PDF_RU . $jahr . "/";
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) if (is_file($dir . $file) && $file > 0) {
				$content = RISPDF2Text::document_text_pdf($dir . $file);
				preg_match("/(?<tag>[0-9]+)\. (?<monat>Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember) $jahr/siu", $content, $datum);

				if (!isset($datum["monat"])) continue;

				$ru    = Rathausumschau::model()->findByAttributes(array("jahr" => $jahr, "nr" => IntVal($file)));
				if (!$ru) {
					$ru        = new Rathausumschau();
					$ru->nr    = IntVal($file);
					$ru->url   = $file;
					$ru->jahr  = $jahr;
					$ru->datum = $jahr . "-" . static::$MONAT_MAPPING[$datum["monat"]] . "-" . $datum["tag"];
					$ru->save();
				}
				$this->parse($ru->id);
			}
			closedir($dh);
		}
	}

	public function parseUpdate()
	{
		$this->parseAlle();
	}
}
