<?php

class Reindex_Stadtrat_TerminCommand extends CConsoleCommand {
	public function run($args) {
		if (!isset($args[0]) || $args[0] <= 1) die("./yiic reindex_stadtrattermin [termin-ID]\n");

		$parser = new StadtratTerminParser();
		$parser->parse($args[0]);
	}
}