<?php

class Reindex_ReferentInnenCommand extends CConsoleCommand
{
    public function run($args)
    {

        $parser = new ReferentInnenParser();
        $parser->parseAlle();

    }
}
