<?php

class Reindex_StadtratCommand extends CConsoleCommand {
	public function run($args) {

		$parser = new StadtratsantragParser();
		$parser->parseAlle();

		$parser = new StadtratTerminParser();
		$parser->parseAlle();
	}
}