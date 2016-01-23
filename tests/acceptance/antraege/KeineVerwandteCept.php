<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Check that an Antrag without Vorgang has no "Verwandte Seiten"');
$I->amOnPage('/antraege/1');
$I->see("Antrag ohne Vorgang");
$I->dontSee('Verwandte Seiten');
