<?php

class Reindex_VorlageCommand extends CConsoleCommand {
	public function run($args) {
		if (count($args) == 0) die("./yii reindex_vorlage [Vorlagen-ID|alle]\n");

		$parser = new StadtratsvorlageParser();
		if ($args[0] == "alle") {
			$parser->parseAlle();
		} else {
			$parser->parse($args[0]);
			/** @var Antrag $a */
			$a = Antrag::model()->findByPk($args[0]);
			$a->resetPersonen();
		}
	}
}