<?php

class Reindex_RathausumschauCommand extends CConsoleCommand {
	public function run($args) {

		$parser = new RathausumschauParser();
		//$parser->parseAlle();
		$parser->parseArchive1(2007);
		//$parser->parseArchive2(2012);
	}
}