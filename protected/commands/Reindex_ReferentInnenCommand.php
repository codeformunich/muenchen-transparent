<?php

class Reindex_ReferentInnenCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (posix_getuid() === 0) die("This command cannot be run as root");
        $parser = new ReferentInnenParser();
        $parser->parseAll();
    }
}
