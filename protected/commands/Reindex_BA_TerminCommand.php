<?php

class Reindex_BA_TerminCommand extends CConsoleCommand {
	public function run($args) {
		if (!isset($args[0]) || $args[0] <= 1) die("./yiic reindex_ba_termin [Termin-ID]\n");

		$parser = new BATerminParser();
		$parser->parse($args[0]);

	}
}