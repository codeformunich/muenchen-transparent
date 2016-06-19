<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:meeting objects (one with and one with a file)');
$I->getOParl('/meeting/1');
$I->getOParl('/meeting/4');
