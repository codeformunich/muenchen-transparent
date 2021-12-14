<?php

class TestTitelKorrekturCommand extends CConsoleCommand
{
    public function run($args)
    {
        $TESTS_TITEL = [
            [
                'input'      => 'Welche Schäden hat der Aufbau des ?Cotton Club? verursacht?',
                'korrigiert' => 'Welche Schäden hat der Aufbau des „Cotton Club“ verursacht?'
            ],
            [
                'input'      => 'Fortschreibung des Standortkonzepts "Kulturstrand" 2015 ff.',
                'korrigiert' => 'Fortschreibung des Standortkonzepts „Kulturstrand“ 2015 ff.'
            ],
        ];

        $TESTS_DOKUMENT = [
            [
                'input'      => 'Neuer Titel',
                'korrigiert' => 'Neuer Titel'
            ],
            [
                'input'      => 'Hinweis fuer Internet',
                'korrigiert' => 'Hinweis für Internet'
            ],
        ];

        $allesok = true;
        foreach ($TESTS_TITEL as $test) {
            $korrektur = RISTools::normalizeTitle($test["input"]);
            if ($korrektur != $test["korrigiert"]) {
                echo "Fehlerhaft:\n";
                echo "- Input: " . $test["input"] . "\n";
                echo "- Erwartet: " . $test["korrigiert"] . "\n";
                echo "- Tatsächlich: " . $korrektur . "\n";
                $allesok = false;
            }
        }
        foreach ($TESTS_DOKUMENT as $test) {
            $korrektur = RISTools::korrigiereDokumentenTitel($test["input"]);
            if ($korrektur != $test["korrigiert"]) {
                echo "Fehlerhaft:\n";
                echo "- Input: " . $test["input"] . "\n";
                echo "- Erwartet: " . $test["korrigiert"] . "\n";
                echo "- Tatsächlich: " . $korrektur . "\n";
                $allesok = false;
            }
        }
        if ($allesok) echo "Alles Ok! 😁\n";
    }
}
