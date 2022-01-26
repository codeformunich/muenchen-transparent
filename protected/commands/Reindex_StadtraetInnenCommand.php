<?php

class Reindex_StadtraetInnenCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (posix_getuid() === 0) die("This command cannot be run as root");
        if (isset($args[0]) && is_numeric($args[0]) && $args[0] > 0) {
            $parser = new StadtraetInnenParser();
            $parser->setParseAlleAntraege(true);
            $parser->parse(intval($args[0]));
        } else {
            $parser = new StadtraetInnenParser();
            $parser->parseAll();
        }
    }
}
