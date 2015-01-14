<?php

class Reindex_Antrag_PersonenCommand extends CConsoleCommand
{
    public function run($args)
    {

        /** @var Antrag[] $antraege */
        $antraege = Antrag::model()->findAll(array("order" => "id", "offset" => 40000, "limit" => 10000));
        for ($i = 0; $i < count($antraege); $i++) {
            echo $i . " / " . count($antraege) . ": " . $antraege[$i]->id . "\n";
            $antraege[$i]->resetPersonen();
        }

    }
}