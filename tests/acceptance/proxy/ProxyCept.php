<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Check that the Dokumentenproxy works correctly with pdf and tiff files');

$I->amOnPage('/media/testdokument.pdf');
$I->seeHTTPHeader('Content-Type', 'application/pdf');
$I->openFile('html/media/testdokument.pdf');
$I->seeInThisFile($I->grabResponse());

$I->amOnPage('/media/testdokument.tiff');
$I->seeHTTPHeader('Content-Type', 'image/tiff');
$I->openFile('html/media/testdokument.tiff');
$I->seeInThisFile($I->grabResponse());
