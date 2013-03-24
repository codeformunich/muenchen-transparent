<?php

class Update_Ris_DailyCommand extends CConsoleCommand {
	public function run($args) {

		$parser = new StadtratTerminParser();
		$parser->parseAlle();

		return;

		$parser = new StadtraetInnenParser();
		//$parser->setParseAlleAntraege(true);
		$parser->parseUpdate();

		$parser = new StadtratTerminParser();
		$parser->parseUpdate();

		$parser = new BATerminParser();
		$parser->parseUpdate();

		$parser = new BAInitiativeParser();
		$parser->parseUpdate();

		$parser = new BAAntragParser();
		$parser->parseUpdate();

		$parser = new StadtratsvorlageParser();
		$parser->parseUpdate();

		$parser = new StadtratsantragParser();
		$parser->parseUpdate();

	}
}