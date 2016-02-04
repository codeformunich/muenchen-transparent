<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Check that a simply Termin-entry works');
$I->amOnPage('/termine/1');

$I->see('Ausschuss mit Terminen (2016-01-01 09:00:00)', 'h1');
$I->seeLink('Original-Seite im RIS', 'http://www.ris-muenchen.de/RII/RII/ris_sitzung_detail.jsp?risid=1');

$I->see('01.01.2016, 09:00',      '#datum');
$I->see('Raum für einen Termin',  '#ort');
$I->see('Ausschuss mit Terminen', '#gremium');

$I->seeLink('Voriger Termin',  '/termine/3');
$I->seeLink('Nächster Termin', '/termine/2');
