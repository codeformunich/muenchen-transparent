<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:person objects (one with all attributes, one with few attributes and one Referent)');
$I->getOParl('/person/1');
$I->getOParl('/person/2');
$I->getOParl('/person/3');
