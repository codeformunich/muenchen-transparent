<?php

class Recalc_StatsCommand extends CConsoleCommand
{
    public function run($args)
    {
        RISMetadaten::recalcStats();
    }
}