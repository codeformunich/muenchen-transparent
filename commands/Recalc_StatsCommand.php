<?php

use app\models\RISMetadaten;

class Recalc_StatsCommand extends ConsoleCommand
{
    public function run($args)
    {
        RISMetadaten::recalcStats();
    }
}