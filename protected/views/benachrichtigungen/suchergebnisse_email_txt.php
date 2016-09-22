<?php
/**
 * @var BenutzerIn $benutzerIn
 * @var array $data
 */
?>Hallo,
seit der letzten E-Mail-Benachrichtigung wurden folgende neuen Dokumente gefunden, die deinen Benachrichtigungseinstellungen entsprechen:

<?

if (count($data["vorgaenge"]) > 0) {
	echo "=== Abonnierte Vorgänge ===\n\n";

	foreach ($data["vorgaenge"] as $vorg) {
		echo $vorg["vorgang"] . "\n";
		foreach ($vorg["neues"] as $item) {
			/** @var IRISItem $item */
			echo "- " . $item->getName(true) . " (" . $item->getLink() . ")\n";
		}
		echo "\n";
	}
}

if (count($data["antraege"]) > 0) {
	echo "=== Anträge & Vorlagen ===\n\n";

	foreach ($data["antraege"] as $dat) {
		/** @var Antrag $antrag */
		$antrag = $dat["antrag"];

		$dokumente_strs = array();
		$queries        = array();
		foreach ($dat["dokumente"] as $dok) {
			/** @var Dokument $dokument */
			$dokument         = $dok["dokument"];
			$dokumente_strs[] = "    - " . $dokument->name . " (" . RIS_URL_PREFIX . $dokument->url . ")";
			foreach ($dok["queries"] as $qu) {
				/** @var RISSucheKrits $qu */
				$name = $qu->getTitle();
				if (!in_array($name, $queries)) $queries[] = $name;
			}
		}

		$name = $antrag->getName();
		$name = preg_replace("/ *(\n *)+/siu", ", ", $name);
		if (strlen($name) > 80) $name = mb_substr($name, 0, 78) . "...";
		echo "- \"" . $name . "\n";
		echo "  " . trim(Yii::app()->createUrl("antraege/anzeigen", array("id" => $antrag->id)), ".") . "\n";
		echo implode("\n", $dokumente_strs);
		if (count($queries) == 1) {
			echo "\n    Gefunden über: \"" . $queries[0] . "\"\n";
		} else {
			echo "\n    Gefunden über: \"" . implode("\", \"", $queries) . "\"\n";
		}
		echo "\n";
	}
}

if (count($data["termine"]) > 0) {
	echo "=== Sitzungen ===\n\n";

foreach ($data["termine"] as $dat) {
	/** @var Termin $termin */
	$termin = $dat["termin"];

	$dokumente_strs = array();
	$queries        = array();
	foreach ($dat["dokumente"] as $dok) {
		/** @var Dokument $dokument */
		$dokument         = $dok["dokument"];
		$dokumente_strs[] = "    - " . $dokument->name . " (" . RIS_URL_PREFIX . $dokument->url . ")";
		foreach ($dok["queries"] as $qu) {
			/** @var RISSucheKrits $qu */
			$name = $qu->getTitle();
			if (!in_array($name, $queries)) $queries[] = $name;
		}
	}

	$name = $termin->getName();
	$name = preg_replace("/ *(\n *)+/siu", ", ", $name);
	if (strlen($name) > 80) $name = mb_substr($name, 0, 78) . "...";
	echo "- \"" . $name . "\n";
	echo "  " . trim(Yii::app()->createUrl("termine/anzeigen", array("id" => $termin->id)), ".") . "\n";
	echo implode("\n", $dokumente_strs);
	if (count($queries) == 1) {
		echo "\n    Gefunden über: \"" . $queries[0] . "\"\n";
	} else {
		echo "\n    Gefunden über: \"" . implode("\", \"", $queries) . "\"\n";
	}
	echo "\n";
}
}
?>
Falls du diese Benachrichtigung nicht mehr erhalten willst, kannst du sie unter <?php echo trim(Yii::app()->createUrl("benachrichtigungen/index", ["code" => $benutzerIn->getBenachrichtigungAbmeldenCode()]), "."); ?> abbestellen.

Liebe Grüße
  Das München Transparent-Team