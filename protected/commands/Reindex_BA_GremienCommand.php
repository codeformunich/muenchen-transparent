<?php

class Reindex_BA_GremienCommand extends CConsoleCommand {
	public function run($args) {


		$parser = new BAGremienParser();
		$parser->parseAlle();

		/*
		if (!isset($args[0]) || $args[0] <= 1) die("./yiic reindex_ba_gremium [Gremium-ID]\n");
		$parser->parse($args[0]);
		*/

	}
}