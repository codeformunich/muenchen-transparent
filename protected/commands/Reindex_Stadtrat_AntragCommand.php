<?php

class Reindex_Stadtrat_AntragCommand extends CConsoleCommand {
	public function run($args) {
		if (count($args[0]) == 0) die("./yii reindex_stadtratantrag [Antrags-ID]");

		$parser = new StadtratsantragParser();
		$parser->parse($args[0]);

		/** @var Antrag $a */
		$a = Antrag::model()->findByPk($args[0]);
		$a->resetPersonen();
	}
}