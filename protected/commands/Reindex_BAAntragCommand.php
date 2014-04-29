<?php

class Reindex_BAAntragCommand extends CConsoleCommand {
	public function run($args) {
		if (!isset($args[0]) || $args[0] <= 1) die("./yiic reindex_baantrag [Antrag-ID]\n");

		$parser = new BAAntragParser();
		$parser->parse($args[0]);

	}
}