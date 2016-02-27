<?php


class Update_RathausumschauCommand extends ConsoleCommand
{
    public function run($args)
    {

        $parser = new RathausumschauParser();
        $parser->parseAlle();

    }
}