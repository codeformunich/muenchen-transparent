<?php

class Reindex_Stadtrat_AntragCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (posix_getuid() === 0) die("This command cannot be run as root");
        if (count($args) == 0) die("./yii reindex_stadtrat_antrag [Antrags-ID|YYYY-MM|alle|ohnereferat]\n");

        $parser = new StadtratsantragParser();
        if ($args[0] === "ohnereferat") {
            /** @var Antrag[] $antraege */
            $antraege = Antrag::model()->findAllByAttributes(["typ" => Antrag::TYP_STADTRAT_ANTRAG, "referat_id" => null]);
            foreach ($antraege as $antrag) $parser->parse($antrag->id);
        } elseif ($args[0] === "alle") {
            $parser->parseAll();
        } elseif (preg_match('/^(?<year>\d{4})-(?<month>\d{2})$/', $args[0], $matches)) {
            $parser->parseMonth(intval($matches['year']), intval($matches['month']));
        } else {
            $parser->parse(intval($args[0]));
            /** @var Antrag $a */
            $a = Antrag::model()->findByPk($args[0]);
            $a->resetPersonen();
        }
    }
}
