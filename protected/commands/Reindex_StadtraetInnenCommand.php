<?php

class Reindex_StadtraetInnenCommand extends CConsoleCommand {
	public function run($args) {

		$parser = new StadtraetInnenParser();
		$parser->parseUpdate();

	}
}