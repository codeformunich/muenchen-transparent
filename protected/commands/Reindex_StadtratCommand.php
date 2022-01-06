<?php

//define("VERYFAST", true);

class Reindex_StadtratCommand extends CConsoleCommand
{
    public function run($args)
    {
        $parser = new TerminParser();
        $parser->parseAll();

        $parser = new StadtratsantragParser();
        $parser->parseAll();

        $parser = new StadtratsvorlageParser();
        $parser->parseAll();

    }
}
