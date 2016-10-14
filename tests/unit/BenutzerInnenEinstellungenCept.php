<?php
$I = new UnitTester($scenario);
$I->wantTo('Test the storage, retrieval and altering user settings');

// Get the test data
require "RISSucheKritsData.php";

$I->haveInDatabase('benutzerInnen', ['id' => '1', 'einstellungen' => '']);
/** @var BenutzerIn $benutzerin */
$benutzerin = BenutzerIn::model()->findByPk(1);

foreach ($krits_array as $krit) {
    $benutzerin->addBenachrichtigung(new RISSucheKrits([$krit]));
}

$krits_array_subdivided = [];
foreach ($krits_array as $krit) {
    if ($krit["typ"] == "antrag_wahlperiode")
        continue;
    $krits_array_subdivided[] = [$krit];
}

$I->assertEquals($krits_array_subdivided, $benutzerin->getEinstellungen()->benachrichtigungen);
$json_raw = ["benachrichtigungen" => $krits_array_subdivided, "benachrichtigungstag" => null];
$I->assertEquals($benutzerin->getEinstellungen()->toJSON(), json_encode($json_raw));

$all_as_kritclass = [];
foreach ($krits_array as $i => $val) {
    if ($val["typ"] == "antrag_wahlperiode")
        continue;
    $all_as_kritclass[] = new RISSucheKrits([$val]);
}
$I->assertEquals($all_as_kritclass, $benutzerin->getBenachrichtigungen());

foreach ($krits_array as $krit) {
    $benutzerin->delBenachrichtigung(new RISSucheKrits([$krit]));
}
$I->assertEquals([], $benutzerin->getEinstellungen()->benachrichtigungen);

// Reset to base setting to test setEinstellungen()
$einstellungen = $benutzerin->getEinstellungen();
$einstellungen->benachrichtigungen = $krits_array_subdivided;
$einstellungen->benachrichtigungstag = 3;
$benutzerin->setEinstellungen($einstellungen);
$json_raw = ["benachrichtigungen" => $krits_array_subdivided, "benachrichtigungstag" => 3];
$I->assertEquals($benutzerin->getEinstellungen()->toJSON(), json_encode($json_raw));

foreach ($krits_array as $krit) {
    if ($krit["typ"] == "antrag_wahlperiode")
        continue;
    $I->assertTrue($benutzerin->wirdBenachrichtigt(new RISSucheKrits([$krit])));
}

$I->assertFalse($benutzerin->wirdBenachrichtigt(new RISSucheKrits()));
