<?php

class Reindex_BA_InitiativeCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (posix_getuid() === 0) die("This command cannot be run as root");
        if (!isset($args[0]) || ($args[0] != "alle" && $args[0] != "ohnereferat" && $args[0] <= 1)) die("./yiic reindex_ba_initiative [BA-Initiative-ID|YYYY|alle]\n");

        $parser = new BAInitiativeParser();
        if ($args[0] == "ohnereferat") {
            /** @var Antrag[] $antraege */
            $antraege = Antrag::model()->findAllByAttributes(["typ" => Antrag::TYP_BA_INITIATIVE, "referat_id" => null]);
            foreach ($antraege as $antrag) $parser->parse($antrag->id);
        } elseif ($args[0] == "alle") {
            $parser->parseAll();
        } elseif (preg_match('/^(?<year>\d{4})-(?<month>\d{2})$/', $args[0], $matches)) {
            $parser->parseMonth(intval($matches['year']), intval($matches['month']));
        } elseif ($args[0] > 0) {
            $parser->parse($args[0]);
        }
    }
}
