<?php

use app\components\RISTools;
use Yii;
use app\models\RISMetadaten;

define("VERYFAST", true);

class Update_Ris_HourlyCommand extends ConsoleCommand
{
    public function run($args)
    {
        echo "Gestartet: " . date("Y-m-d H:i:s") . "\n";


        try {
            $parser = new ReferentInnenParser();
            $parser->parseQuickUpdate();

            echo "Done ReferentInnen: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::send_email(Yii::$app->params['adminEmail'], "RIS Exception ReferentIn", print_r($e, true), null, "system");
        }


        try {
            $parser = new StadtratTerminParser();
            $parser->parseQuickUpdate();

            echo "Done Termine: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::send_email(Yii::$app->params['adminEmail'], "RIS Exception Stadtrattermin", print_r($e, true), null, "system");
        }


        try {
            $parser = new StadtratsvorlageParser();
            $parser->parseQuickUpdate();

            echo "Done Vorlagen: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::send_email(Yii::$app->params['adminEmail'], "RIS Exception Vorlagen", print_r($e, true), null, "system");
        }


        try {
            $parser = new StadtratsantragParser();
            $parser->parseQuickUpdate();

            echo "Done Stadtratsanträge: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::send_email(Yii::$app->params['adminEmail'], "RIS Exception StR-Anträge", print_r($e, true), null, "system");
        }


        try {
            $parser = new StadtraetInnenParser();
            //$parser->setParseAlleAntraege(true);
            $parser->parseQuickUpdate();

            echo "Done StadträtInnen: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::send_email(Yii::$app->params['adminEmail'], "RIS Exception StadträtInnen", print_r($e, true), null, "system");
        }


        try {
            $parser = new BATerminParser();
            $parser->parseQuickUpdate();

            echo "Done BA Termine: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::send_email(Yii::$app->params['adminEmail'], "RIS Exception BA-Termine", print_r($e, true), null, "system");
        }


        try {
            $parser = new BAInitiativeParser();
            $parser->parseQuickUpdate();

            echo "Done BA Initiative: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::send_email(Yii::$app->params['adminEmail'], "RIS Exception BA-Initiative", print_r($e, true), null, "system");
        }


        try {
            $parser = new BAAntragParser();
            $parser->parseQuickUpdate();

            echo "Done BA Anträge: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::send_email(Yii::$app->params['adminEmail'], "RIS Exception BA-Anträge", print_r($e, true), null, "system");
        }


        RISMetadaten::setzeLetzteAktualisierung(date("Y-m-d H:i:s"));
        RISMetadaten::recalcStats();

        echo "Done: " . date("Y-m-d H:i:s") . "\n";

    }
}
