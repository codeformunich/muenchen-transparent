<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:body objects (the Stadtrat and one BA)');
$I->getOParl('/body/0');
$I->getOParl('/body/1');
