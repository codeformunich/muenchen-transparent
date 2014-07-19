<?php

class Reindex_BA_AntragCommand extends CConsoleCommand {
	public function run($args) {
		if (!isset($args[0]) || ($args[0] != "alle" && $args[0] <= 1)) die("./yiic reindex_baantrag [termin-ID]|alle\n");

		$parser = new BAAntragParser();
		if ($args[0] == "alle") $parser->parseAlle();
		if ($args[0] > 0) $parser->parse($args[0]);
	}
}