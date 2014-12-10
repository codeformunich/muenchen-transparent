<?php

//define("VERYFAST", true);

class Update_Ris_DailyCommand extends CConsoleCommand
{
	public function run($args)
	{
		echo "Gestartet: " . date("Y-m-d H:i:s");


		try {
			$parser = new ReferentInnenParser();
			$parser->parseUpdate();

			echo "Done ReferentInnen: " . date("Y-m-d H:i:s");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception ReferentIn", print_r($e, true), null, "system");
		}


		try {
			$parser = new StadtratTerminParser();
			$parser->parseUpdate();

			echo "Done Termine: " . date("Y-m-d H:i:s");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception Stadtrattermin", print_r($e, true), null, "system");
		}


		try {
			$parser = new StadtratsvorlageParser();
			$parser->parseUpdate();

			echo "Done Vorlagen: " . date("Y-m-d H:i:s");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception Vorlagen", print_r($e, true), null, "system");
		}


		try {
			$parser = new StadtratsantragParser();
			$parser->parseUpdate();

			echo "Done Stadtratsanträge: " . date("Y-m-d H:i:s");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception StR-Anträge", print_r($e, true), null, "system");
		}


		try {
			$parser = new StadtraetInnenParser();
			//$parser->setParseAlleAntraege(true);
			$parser->parseUpdate();

			echo "Done StadträtInnen: " . date("Y-m-d H:i:s");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception StadträtInnen", print_r($e, true), null, "system");
		}


		try {
			$parser = new BATerminParser();
			$parser->parseUpdate();

			echo "Done BA Termine: " . date("Y-m-d H:i:s");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception BA-Termine", print_r($e, true), null, "system");
		}


		try {
			$parser = new BAInitiativeParser();
			$parser->parseUpdate();

			echo "Done BA Initiative: " . date("Y-m-d H:i:s");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception BA-Initiative", print_r($e, true), null, "system");
		}


		try {
			$parser = new BAAntragParser();
			$parser->parseUpdate();

			echo "Done BA Anträge: " . date("Y-m-d H:i:s");
		} catch (Exception $e) {
			RISTools::send_email(Yii::app()->params['adminEmail'], "RIS Exception BA-Anträge", print_r($e, true), null, "system");
		}


		RISMetadaten::setzeLetzteAktualisierung(date("Y-m-d H:i:s"));
		RISMetadaten::recalcStats();

		echo "Done: " . date("Y-m-d H:i:s");

	}
}