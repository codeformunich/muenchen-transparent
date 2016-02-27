<?php


class Reindex_BACommand extends ConsoleCommand
{
    public function run($args)
    {
        $parser = new BAMitgliederParser();
        $parser->parseAlle();

        $parser = new BAGremienParser();
        $parser->parseAlle();

        $parser = new BATerminParser();
        $parser->parseAlle();

        $parser = new BAAntragParser();
        $parser->parseAlle();

        $parser = new BAInitiativeParser();
        $parser->parseAlle();

    }
}