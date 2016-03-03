<?php 


$I = new AcceptanceTester($scenario);
$I->wantTo('Check quick and dirty (@YII2_TODO) that the Themenverwandte Page works');
$I->amOnPage('/antraege/themenverwandte/3420550');
$I->see('Möglicherweise verwandte Dokumente');
$I->see('Entminung der Fröttmaninger Heide');
