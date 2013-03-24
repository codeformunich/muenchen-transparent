<?php

class Update_StadtratsantragCommand extends CConsoleCommand {
	public function run($args) {

		$parser = new StadtratsantragParser();
		if (isset($args[0]) && $args[0] > 0) $parser->parse($args[0]);
		elseif (isset($args[0]) && $args[0] == "alle") $parser->parseAlle();
		else echo "./yiic update_stadtratsantrag [id|alle]\n";

	}
}