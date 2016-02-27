<?php

use app\models\Antrag;

class Reindex_BA_AntragCommand extends ConsoleCommand
{
    public function run($args)
    {
        if (!isset($args[0]) || ($args[0] != "alle" && $args[0] != "ohnereferat" && $args[0] <= 1)) die("./yiic reindex_ba_antrag [BA-Antrag-ID]|alle\n");

        $parser = new BAAntragParser();
        if ($args[0] == "ohnereferat") {
            /** @var Antrag[] $antraege */
            $antraege = Antrag::findAll(["typ" => Antrag::$TYP_BA_ANTRAG, "referat_id" => null]);
            foreach ($antraege as $antrag) $parser->parse($antrag->id);
        } elseif ($args[0] == "alle") {
            $parser->parseAlle();
        } elseif ($args[0] > 0) {
            $parser->parse($args[0]);
        }
    }
}