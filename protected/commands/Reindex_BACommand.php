<?php

class Reindex_BACommand extends CConsoleCommand {
	public function run($args) {
		$parser = new BAAntragParser();
		$parser->parseAlle();

		$parser = new BAInitiativeParser();
		$parser->parseAlle();

		$parser = new BATerminParser();
		$parser->parseAlle();


	}
}