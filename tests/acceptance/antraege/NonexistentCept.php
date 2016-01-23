<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Check that a non-existent Antrag gives a 404');
$I->amOnPage('/antraege/0');
$I->seePageNotFound();
