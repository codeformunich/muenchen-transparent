<?php

class Update_Ris_DailyCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (posix_getuid() === 0) die("This command cannot be run as root");

        echo "Gestartet: " . date("Y-m-d H:i:s");

        try {
            $parser = new ReferentInnenParser();
            $parser->parseUpdate();

            echo "Done ReferentInnen: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::report_ris_parser_error("RIS Exception ReferentIn", print_r($e, true));
        }


        try {
            $parser = new TerminParser();
            $parser->parseUpdate();

            echo "Done Termine: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::report_ris_parser_error("RIS Exception Stadtrattermin", print_r($e, true));
        }


        try {
            $parser = new StadtratsvorlageParser();
            $parser->parseUpdate();

            echo "Done Vorlagen: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::report_ris_parser_error("RIS Exception Vorlagen", print_r($e, true));
        }


        try {
            $parser = new StadtratsantragParser();
            $parser->parseUpdate();

            echo "Done Stadtratsanträge: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::report_ris_parser_error("RIS Exception StR-Anträge", print_r($e, true));
        }


        try {
            $parser = new StadtraetInnenParser();
            //$parser->setParseAlleAntraege(true);
            $parser->parseUpdate();

            echo "Done StadträtInnen: " . date("Y-m-d H:i:s") . "\n";
        } catch (Exception $e) {
            RISTools::report_ris_parser_error("RIS Exception StadträtInnen", print_r($e, true));
        }

        try {
            $parser = new BAInitiativeParser();
            $parser->parseUpdate();

            echo "Done BA Initiative: " . date("Y-m-d H:i:s");
        } catch (Exception $e) {
            RISTools::report_ris_parser_error("RIS Exception BA-Initiative", print_r($e, true));
        }

        try {
            $parser = new BAAntragParser();
            $parser->parseUpdate();

            echo "Done BA Anträge: " . date("Y-m-d H:i:s");
        } catch (Exception $e) {
            RISTools::report_ris_parser_error("RIS Exception BA-Anträge", print_r($e, true));
        }


        RISMetadaten::setzeLetzteAktualisierung(date("Y-m-d H:i:s"));
        RISMetadaten::recalcStats();

        echo "Done: " . date("Y-m-d H:i:s") . "\n";
    }
}
