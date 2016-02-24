<?php

class Reindex_Stadtrat_VorlageCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (count($args) == 0) die("./yii reindex_vorlage [Vorlagen-ID|alle]\n");

        $parser = new StadtratsvorlageParser();
        if ($args[0] == "ohnereferat") {
            /** @var Antrag[] $antraege */
            $antraege = Antrag::model()->findAllByAttributes(["typ" => Antrag::$TYP_STADTRAT_VORLAGE, "referat_id" => null]);
            foreach ($antraege as $antrag) $parser->parse($antrag->id);
        } elseif ($args[0] == "alle") {
            $parser->parseAlle();
        } else {
            $parser->parse($args[0]);
            /** @var Antrag $a */
            $a = Antrag::model()->findByPk($args[0]);
            if ($a) $a->resetPersonen();
        }
    }
}