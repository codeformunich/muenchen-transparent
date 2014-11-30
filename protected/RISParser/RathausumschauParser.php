<?php

class RathausumschauParser extends RISParser
{

	public static $URL_AKTUELLES_JAHR = "http://www.muenchen.de/rathaus/Stadtinfos/Presse-Service/Presse-Archiv/2014.html";

	public function parse($id)
	{
		echo $id;
	}

	public function parseSeite($seite, $first)
	{

	}

	public function parseAlle()
	{
		$url = ris_download_string(static::$URL_AKTUELLES_JAHR);
		preg_match_all("/Rathaus Umschau (?<nr>[0-9]+) vom (?<datum>[0-9\.]+)&nbsp[^<]+<a href=\"(?<url>[^\"]+)\"/siu", $url, $matches);


		for ($i = 0; $i < count($matches["url"]); $i++) {
			$datum = explode(".", $matches["datum"][$i]);
			var_dump($datum);
			$ru = Rathausumschau::model()->findByAttributes(array("jahr" => $datum[2], "nr" => $matches["nr"][$i]));
			if (!$ru) {
				$ru = new Rathausumschau();
				$ru->nr = $matches["nr"][$i];
				$ru->url = $matches["url"][$i];
				$ru->jahr = $datum[2];
				$ru->datum = $datum[2] . "-" . $datum[1] . "-" . $datum[0];
				$ru->save();
			}
			$this->parse($ru->id);
		}
	}

	public function parseUpdate()
	{
		$this->parseAlle();
	}
}