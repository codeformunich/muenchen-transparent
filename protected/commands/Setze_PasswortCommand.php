<?php

class Setze_PasswortCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (count($args) != 2) die("./yii setze_passwort E-Mail-Adresse Neues-Passwort\n");

        /** @var BenutzerIn $benutzerIn */
        $benutzerIn = BenutzerIn::model()->findByAttributes(array("email" => $args[0]));
        if (!$benutzerIn) {
            echo "KeinE BenutzerIn mit dieser E-Mail-Adresse gefunden.\n";
            return;
        }

        $benutzerIn->setPassword($args[1]);
    }
}