<?php
$I = new UnitTester($scenario);
$I->wantTo('Test the storage, retrieval and altering user settings');

// Get the test data
require "RISSucheKritsData.php";
$krits_array_benachritigungen = (new RISSucheKrits($krits_array))->getBenachrichtigungKrits()->krits;

// Init
$I->haveInDatabase('benutzerInnen', ['id' => '1', 'einstellungen' => '']);
/** @var BenutzerIn $benutzerin */
$benutzerin = BenutzerIn::model()->findByPk(1);

// Test addBenachrichtigung() by adding all krits
foreach ($krits_array as $krit) {
    $benutzerin->addBenachrichtigung(new RISSucheKrits([$krit]));
}

$krits_array_subdivided = [];
foreach ($krits_array_benachritigungen as $krit) {
    $krits_array_subdivided[] = [$krit];
}

// Test getEinstellungen()
$I->assertEquals($krits_array_subdivided, $benutzerin->getEinstellungen()->benachrichtigungen);
$json_raw = ["benachrichtigungen" => $krits_array_subdivided, "benachrichtigungstag" => null];
$I->assertEquals($benutzerin->getEinstellungen()->toJSON(), json_encode($json_raw));

// Test wirdBenachrichtigt()
foreach ($krits_array as $krit) {
    if ($krit["typ"] == "antrag_wahlperiode")
        continue;
    $I->assertTrue($benutzerin->wirdBenachrichtigt(new RISSucheKrits([$krit])));
}

$I->assertFalse($benutzerin->wirdBenachrichtigt(new RISSucheKrits()));

// Test getBenachrichtigungen()
$all_as_kritclass = [];
foreach ($krits_array_benachritigungen as $krit) {
    $all_as_kritclass[] = new RISSucheKrits([$krit]);
}
$I->assertEquals($all_as_kritclass, $benutzerin->getBenachrichtigungen());

// Test delBenachrichtigung() by removing all krits
foreach ($krits_array as $krit) {
    $benutzerin->delBenachrichtigung(new RISSucheKrits([$krit]));
}
$I->assertEquals([], $benutzerin->getEinstellungen()->benachrichtigungen);

// Test setting the benachrichtigungstag
$einstellungen = $benutzerin->getEinstellungen();
$einstellungen->benachrichtigungstag = 3;
$benutzerin->setEinstellungen($einstellungen);
$json_raw = ["benachrichtigungen" => [], "benachrichtigungstag" => 3];
$I->assertEquals($benutzerin->getEinstellungen()->toJSON(), json_encode($json_raw));

