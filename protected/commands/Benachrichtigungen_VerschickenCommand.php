<?php

class Benachrichtigungen_VerschickenCommand extends CConsoleCommand {
	public function run($args) {
		/** @var BenutzerIn[] $benutzerInnen */
		$benutzerInnen = BenutzerIn::model()->findAll();
		foreach ($benutzerInnen as $benutzerIn) {
			$benutzerIn->verschickeNeueBenachrichtigungen();
		}
	}
}