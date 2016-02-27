<?php


//define("VERYFAST", true);

class Reindex_RechtCommand extends ConsoleCommand
{
    public function run($args)
    {
        $parser = new StadtrechtParser();
        $parser->parseAlle();
    }
}
