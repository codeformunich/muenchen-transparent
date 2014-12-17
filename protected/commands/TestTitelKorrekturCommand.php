<?php

class TestTitelKorrekturCommand extends CConsoleCommand
{
	public function run($args)
	{
		$TESTS = array(
			array(
				'input' => 'Welche Schäden hat der Aufbau des ?Cotton Club? verursacht?',
				'korrigiert' => 'Welche Schäden hat der Aufbau des „Cotton Club“ verursacht?'
			),
			array(
				'input' => 'Fortschreibung des Standortkonzepts "Kulturstrand" 2015 ff.',
				'korrigiert' => 'Fortschreibung des Standortkonzepts „Kulturstrand“ 2015 ff.'
			),
		);

		$allesok = true;
		foreach ($TESTS as $test) {
			$korrektur = RISTools::korrigiereTitelZeichen($test["input"]);
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
