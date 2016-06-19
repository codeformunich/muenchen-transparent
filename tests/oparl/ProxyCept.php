<?php
$I = new OparlTester($scenario);
$I->wantTo('Ensure that the proxy for files works and sets the right headers (pdf, tiff, non-existing document, file gone)');

// pdf
$I->amOnPage('/fileaccess/access/7');
$I->seeResponseCodeIs(200);
$I->seeHttpHeader('Content-Type', 'application/pdf');
$I->dontSeeHttpHeader('Content-Disposition', 'attachment; filename="7 - Dokument (pdf) mit vielen Eigenschaften.pdf"');

$I->amOnPage('/fileaccess/download/7');
$I->seeResponseCodeIs(200);
$I->seeHttpHeader('Content-Type', 'application/pdf');
$I->seeHttpHeader('Content-Disposition', 'attachment; filename="7 - Dokument (pdf) mit vielen Eigenschaften.pdf"');

// tiff
$I->amOnPage('/fileaccess/access/8');
$I->seeResponseCodeIs(200);
$I->seeHttpHeader('Content-Type', 'image/tiff');
$I->dontSeeHttpHeader('Content-Disposition', 'attachment; filename="8 - Dokument (tiff) mit wenig Eigenschaften.pdf"');

$I->amOnPage('/fileaccess/download/8');
$I->seeResponseCodeIs(200);
$I->seeHttpHeader('Content-Type', 'image/tiff');
$I->seeHttpHeader('Content-Disposition', 'attachment; filename="8 - Dokument (tiff) mit wenig Eigenschaften.pdf"');

// none-existing document
$I->amOnPage('/fileaccess/access/9001');
$I->seeResponseCodeIs(404);

$I->amOnPage('/fileaccess/download/9001');
$I->seeResponseCodeIs(404);

// file gone
$I->amOnPage('/fileaccess/access/10');
$I->seeResponseCodeIs(410);

$I->amOnPage('/fileaccess/download/10');
$I->seeResponseCodeIs(410);