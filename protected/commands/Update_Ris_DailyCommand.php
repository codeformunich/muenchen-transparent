<?php

class Update_Ris_DailyCommand extends CConsoleCommand
{
	public function run($args)
	{
		$fp = fopen("/tmp/ris_daily.log", "a");
		fwrite($fp, "Gestartet: " . date("Y-m-d H:i:s") . "\n");


		try {
			$parser = new StadtratsvorlageParser();
			$parser->parseUpdate();

			fwrite($fp, "Done Vorlagen: " . date("Y-m-d H:i:s") . "\n");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception Vorlagen", print_r($e, true));
		}

		try {
			$parser = new StadtratsantragParser();
			$parser->parseUpdate();

			fwrite($fp, "Done Stadtratsanträge: " . date("Y-m-d H:i:s") . "\n");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception StR-Anträge", print_r($e, true));
		}

		try {
			$parser = new StadtraetInnenParser();
			//$parser->setParseAlleAntraege(true);
			$parser->parseUpdate();

			fwrite($fp, "Done StadträtInnen: " . date("Y-m-d H:i:s") . "\n");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception StadträtInnen", print_r($e, true));
		}


		try {
			$parser = new StadtratTerminParser();
			$parser->parseUpdate();

			fwrite($fp, "Done Termine: " . date("Y-m-d H:i:s") . "\n");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception Stadtrattermin", print_r($e, true));
		}

		try {
			$parser = new BATerminParser();
			$parser->parseUpdate();

			fwrite($fp, "Done BA Termine: " . date("Y-m-d H:i:s") . "\n");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception BA-Termine", print_r($e, true));
		}


		try {
			$parser = new BAInitiativeParser();
			$parser->parseUpdate();

			fwrite($fp, "Done BA Initiative: " . date("Y-m-d H:i:s") . "\n");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception BA-Initiative", print_r($e, true));
		}


		try {
			$parser = new BAAntragParser();
			$parser->parseUpdate();

			fwrite($fp, "Done BA Anträge: " . date("Y-m-d H:i:s") . "\n");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception BA-Anträge", print_r($e, true));
		}


		RISMetadaten::setzeLetzteAktualisierung(date("Y-m-d H:i:s"));

		fwrite($fp, "Done: " . date("Y-m-d H:i:s") . "\n\n");

	}
}