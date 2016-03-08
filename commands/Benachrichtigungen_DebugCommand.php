<?php

use app\models\BenutzerIn;

class Benachrichtigungen_DebugCommand extends ConsoleCommand
{
    public function run($args)
    {
        if (count($args) != 2) {
            die("./yiic benachrichtigungen_verschicken [e@mail tage]\n");
        }

        if (is_numeric($args[0])) {
            $benutzerIn = BenutzerIn::findOne($args[0]);
        } else {
            $benutzerIn = BenutzerIn::findOne(["email" => $args[0]]);
        }
        if (!$benutzerIn) {
            die("BenutzerIn nicht gefunden.\n");
        }
        /** @var BenutzerIn $benutzerIn */
        $ergebnisse = $benutzerIn->benachrichtigungsErgebnisse($args[1]);

        if (isset($ergebnisse["antraege"])) foreach ($ergebnisse["antraege"] as $antr) {
            $antrag = $antr["antrag"];
            /** @var Antrag $antrag */
            echo "Antrag: " . $antrag->id . " - " . $antrag->getName() . "\n";
            foreach ($antr["dokumente"] as $dok) {
                $dokument = $dok["dokument"];
                /** @var Dokument $dokument */
                echo " - Dokument: " . $dokument->id . " - " . $dokument->name_title . " - ". $dokument->getLink() . "\n";
                foreach ($dok["queries"] as $qu) {
                    /** @var RISSucheKrits $qu */
                    echo "   - " . $qu->getTitle($dokument) . "\n";
                }
            }
        }

        if (isset($ergebnisse["termine"])) foreach ($ergebnisse["termine"] as $antr) {
            $termin = $antr["termin"];
            /** @var Termin $termin */
            echo "Termin: " . $termin->id . " - " . $termin->getName() . "\n";
            foreach ($antr["dokumente"] as $dok) {
                $dokument = $dok["dokument"];
                /** @var Dokument $dokument */
                echo " - Dokument: " . $dokument->id . " - " . $dokument->name_title . " - ". $dokument->getLink() . "\n";
                foreach ($dok["queries"] as $qu) {
                    /** @var RISSucheKrits $qu */
                    echo "   - " . $qu->getTitle($dokument) . "\n";
                }
            }
        }
    }
}
