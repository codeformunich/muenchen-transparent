<?php

class Update_RathausumschauCommand extends CConsoleCommand
{
    public function run($args)
    {

        $parser = new RathausumschauParser();
        $parser->parseAlle();

    }
}