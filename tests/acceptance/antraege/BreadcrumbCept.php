<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Check that a Dokument has the correct Breadcrumb');
$I->amOnPage("/dokumente/3"); // No validation here yet
$I->seeLink('Antragsseite', '/antraege/4');
$I->seeLink('Ein Dokument von mehreren ein einem Antrag', '/dokumente/3');
$I->seeLink('Ein anderes Dokument von mehreren ein einem Antrag', '/dokumente/4');

$I->amOnPage("/dokumente/5"); // No validation here yet
$I->seeLink('Antragsseite', '/antraege/5');
$I->see('Ein Dokument von einem Antrag mit einem Dokument');
$I->dontSeeLink('Ein Dokument von einem Antrag mit einem Dokument', '/dokumente/5');

$I->amOnPage("/dokumente/6"); // No validation here yet
$I->dontSee('Antragsseite');
$I->see('Dokument ohne Antrag');
$I->dontSeeLink('Dokument ohne Antrag', '/dokumente/6');
