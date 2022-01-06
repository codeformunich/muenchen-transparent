<?php

define("VERYFAST", true);

class Reindex_TerminCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (!isset($args[0]) || ($args[0] != "alle" && $args[0] <= 1)) die("./yiic reindex_termin [termin-ID]|YYYY-MM|alle\n");

        $parser = new TerminParser();
        if ($args[0] === "alle") {
            $parser->parseAll();
        } elseif (preg_match('/^(?<year>\d{4})-(?<month>\d{2})$/', $args[0], $matches)) {
            $parser->parseMonth(intval($matches['year']), intval($matches['month']));
        } elseif ($args[0] > 0) {
            $parser->parse(intval($args[0]));
        }
    }
}
