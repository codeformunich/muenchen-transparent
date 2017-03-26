<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:file objects (one with many attributes, one with few attributes and one Rathausumschau)');
$I->getOParl('/file/7');

$I->amOnPage($I->getResponseAsTree()->accessUrl);
$I->seeResponseCodeIs(200);
$I->seeHttpHeader('Content-Type', 'application/pdf');
$I->dontSeeHttpHeader('Content-Disposition', 'attachment; filename="7 - Dokument (pdf) mit vielen Eigenschaften.pdf"');

$I->amOnPage($I->getResponseAsTree()->downloadUrl);
$I->seeResponseCodeIs(200);
$I->seeHttpHeader('Content-Type', 'application/pdf');
$I->seeHttpHeader('Content-Disposition', 'attachment; filename="7 - Dokument (pdf) mit vielen Eigenschaften.pdf"');

$I->getOParl('/file/8');
