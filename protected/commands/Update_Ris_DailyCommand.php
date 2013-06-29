<?php

class Update_Ris_DailyCommand extends CConsoleCommand {
	public function run($args) {
		$fp = fopen("/tmp/ris_daily.log", "a");
		fwrite($fp, "Gestartet: " . date("Y-m-d H:i:s") . "\n");

		$parser = new StadtratsvorlageParser();
		$parser->parseUpdate();

		fwrite($fp, "Done Vorlagen: " . date("Y-m-d H:i:s") . "\n");

		$parser = new StadtratsantragParser();
		$parser->parseUpdate();

		fwrite($fp, "Done Stadtratsanträge: " . date("Y-m-d H:i:s") . "\n");

		$parser = new StadtraetInnenParser();
		//$parser->setParseAlleAntraege(true);
		$parser->parseUpdate();

		fwrite($fp, "Done StadträtInnen: " . date("Y-m-d H:i:s") . "\n");

		$parser = new StadtratTerminParser();
		$parser->parseUpdate();

		fwrite($fp, "Done Termine: " . date("Y-m-d H:i:s") . "\n");

		$parser = new BATerminParser();
		$parser->parseUpdate();

		fwrite($fp, "Done BA Termine: " . date("Y-m-d H:i:s") . "\n");

		$parser = new BAInitiativeParser();
		$parser->parseUpdate();

		fwrite($fp, "Done BA Initiative: " . date("Y-m-d H:i:s") . "\n");

		$parser = new BAAntragParser();
		$parser->parseUpdate();

		fwrite($fp, "Done BA Anträge: " . date("Y-m-d H:i:s") . "\n");

		RISMetadaten::setzeLetzteAktualisierung(date("Y-m-d H:i:s"));

		fwrite($fp, "Done: " . date("Y-m-d H:i:s") . "\n\n");

	}
}