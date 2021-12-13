<?php

class Reindex_BACommand extends CConsoleCommand
{
    public function run($args)
    {
        $parser = new BAMitgliederParser();
        $parser->parseAll();

        $parser = new BAGremienParser();
        $parser->parseAll();

        $parser = new BATerminParser();
        $parser->parseAll();

        $parser = new BAAntragParser();
        $parser->parseAll();

        $parser = new BAInitiativeParser();
        $parser->parseAll();

    }
}
