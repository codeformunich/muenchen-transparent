<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Check that an Antrag has the correct "Verwandte Seiten"');
$I->amOnPageValidated('/antraege/2');
$I->see('Antrag mit verwandten Seiten');
$I->seeLink('Das Dokument zum Antrag mit verwandten Seiten', '/dokumente/2');
$I->see('Verwandte Seiten');
$I->seeLink('Ein verwandter Antrag', '/antraege/3');
$I->dontSeeLink('Ein verwandtes Dokument', '/dokumente/1');
$I->dontSeeLink('Antrag mit verwandten Seiten', '/antraege/2');
$I->dontSee('Das Dokument zum Antrag mit verwandten Seiten', '#verwandte_seiten');

$I->amOnPageValidated('/antraege/1');
$I->see('Antrag ohne Vorgang');
$I->dontSee('Verwandte Seiten');
