<?php

use app\models\BenutzerIn;

class Setze_PasswortCommand extends ConsoleCommand
{
    public function run($args)
    {
        if (count($args) != 2) die("./yii setze_passwort E-Mail-Adresse Neues-Passwort\n");

        /** @var BenutzerIn $benutzerIn */
        $benutzerIn = BenutzerIn::findOne(["email" => $args[0]]);
        if (!$benutzerIn) {
            echo "KeinE BenutzerIn mit dieser E-Mail-Adresse gefunden.\n";
            return;
        }

        $benutzerIn->setPassword($args[1]);
    }
}