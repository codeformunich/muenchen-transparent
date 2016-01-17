<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('log in');
$I->amOnPage('/benachrichtigungen');
$I->fillField('email', 'user@example.com');
$I->fillField('password', '1234');
//$I->fillField('#login', '');
//$I->click('#login');
$I->submitForm('#login', [], $I->grabAttributeFrom('#login', 'name'));
$I->see('Benachrichtigungen an user@example.com:');
