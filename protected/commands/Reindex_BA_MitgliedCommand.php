<?php

class Reindex_BA_MitgliedCommand extends CConsoleCommand
{
	public function run($args)
	{
		if (!isset($args[0]) || ($args[0] != "alle"&& $args[0] <= 1)) die("./yiic reindex_ba_mitglied [BA-Nr]|alle\n");

		$parser = new BAMitgliederParser();
		if ($args[0] == "alle") {
			$parser->parseAlle();
		} elseif ($args[0] > 0) {
			$parser->parse($args[0]);
		}
	}
}