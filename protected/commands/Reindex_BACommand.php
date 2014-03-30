<?php

class Reindex_BACommand extends CConsoleCommand {
	public function run($args) {
		$parser = new BAInitiativeParser();
		$parser->parseAlle();

		$parser = new StadtratTerminParser();
		$parser->parseAlle();

		$parser = new BATerminParser();
		$parser->parseAlle();

		$parser = new BAAntragParser();
		$parser->parseAlle();

	}
}