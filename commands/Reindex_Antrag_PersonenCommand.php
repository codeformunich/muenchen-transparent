<?php

use app\models\Antrag;

class Reindex_Antrag_PersonenCommand extends ConsoleCommand
{
    public function run($args)
    {

        /** @var Antrag[] $antraege */
        $antraege = Antrag::find()->findAll(["order" => "id", "offset" => 40000, "limit" => 10000]);
        for ($i = 0; $i < count($antraege); $i++) {
            echo $i . " / " . count($antraege) . ": " . $antraege[$i]->id . "\n";
            $antraege[$i]->resetPersonen();
        }

    }
}