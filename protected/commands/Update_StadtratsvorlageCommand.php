<?php

class Update_StadtratsvorlageCommand extends CConsoleCommand {
	public function run($args) {

		if (!isset($args[0])) die("./yii update_stadtratsvorlage [id]\n");

		$parser = new StadtratsvorlageParser();
		$parser->parse($args[0]);
	}
}
