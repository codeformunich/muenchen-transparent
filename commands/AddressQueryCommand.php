<?php


//define("VERYFAST", true);

class AddressQueryCommand extends ConsoleCommand
{
    public function run($args)
    {
        if (count($args) != 1) die("Usage: ./yiic addressquery \"Adresse\"\n");
        var_dump(ris_intern_address2geo("Deutschland", "", "München", $args[0]));
    }
}
