<?php

//define("VERYFAST", true);

class Reindex_RechtCommand extends CConsoleCommand
{
    public function run($args)
    {
        $parser = new StadtrechtParser();
        $parser->parseAlle();
    }
}
