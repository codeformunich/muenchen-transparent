<?php


class Reindex_RathausumschauCommand extends ConsoleCommand
{
    public function run($args)
    {

        $parser = new RathausumschauParser();
        $parser->parseAlle();
        //$parser->parseArchive1(2008);
        //$parser->parseArchive2(2009);
        //$parser->parseArchive3(2013);
    }
}