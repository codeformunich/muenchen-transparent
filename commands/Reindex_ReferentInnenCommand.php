<?php


class Reindex_ReferentInnenCommand extends ConsoleCommand
{
    public function run($args)
    {

        $parser = new ReferentInnenParser();
        $parser->parseAlle();

    }
}