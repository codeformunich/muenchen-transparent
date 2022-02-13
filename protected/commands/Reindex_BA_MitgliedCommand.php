<?php

class Reindex_BA_MitgliedCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (posix_getuid() === 0) die("This command cannot be run as root");
        if (!isset($args[0]) || ($args[0] !== "alle" && $args[0] <= 1)) die("./yiic reindex_ba_mitglied [Mitglied-ID]|alle\n");

        $parser = new BAMitgliederParser();
        if ($args[0] === "alle") {
            $parser->parseAll();
        } else {
            $parser->parse($args[0]);
        }
    }
}
