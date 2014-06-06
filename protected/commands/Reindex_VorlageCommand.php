<?php

class Reindex_VorlageCommand extends CConsoleCommand {
	public function run($args) {
		if (count($args[0]) == 0) die("./yii reindex_vorlage [Vorlagen-ID]");

		$parser = new StadtratsvorlageParser();

		/*
		$crit = new CDbCriteria();
		$crit->condition = "typ='" . addslashes(Antrag::$TYP_STADTRAT_VORLAGE) . "' AND id >= 26929";
		$antraege = Antrag::model()->findAll($crit);
		foreach ($antraege as $a) $parser->parse($a->id);

		return;

		*/
		$parser->parse($args[0]);

		/** @var Antrag $a */
		$a = Antrag::model()->findByPk($args[0]);
		$a->resetPersonen();
	}
}