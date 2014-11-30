<?php

class Reindex_RathausumschauCommand extends CConsoleCommand {
	public function run($args) {

		$parser = new RathausumschauParser();
		$parser->parseAlle();
	}
}