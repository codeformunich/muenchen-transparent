<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Check that a non-existent Antrag gives a 404');
$I->amOnPageValidated('/antraege/0');
$I->seePageNotFound();
