<?php

define("VERYFAST", true);

class Reindex_Stadtrat_TerminCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (!isset($args[0]) || ($args[0] != "alle" && $args[0] <= 1)) die("./yiic reindex_stadtrattermin [termin-ID]|YYYY-MM|alle\n");

        $parser = new StadtratTerminParser();
        if ($args[0] == "alle") {
            $parser->parseAll();
        }
        if (preg_match('/^(?<year>\d{4})-(?<month>\d{2})$/', $args[0], $matches)) {
            $parser->parseMonth(intval($matches['year']), intval($matches['month']));
        }
        if ($args[0] > 0) {
            $parser->parse($args[0]);
        }
    }
}
