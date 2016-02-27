<?php


//define("VERYFAST", true);

class Reindex_StadtratCommand extends ConsoleCommand
{
    public function run($args)
    {
        $parser = new StadtratTerminParser();
        $parser->parseAlle();

        $parser = new StadtratsantragParser();
        $parser->parseAlle();

        $parser = new StadtratsvorlageParser();
        $parser->parseAlle();

    }
}