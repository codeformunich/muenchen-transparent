<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:organization objects (one "Fraktion", one "BA-Gremium" and one "Referat")');
$I->getOParl('/organization/fraktion/1');
$I->getOParl('/organization/gremium/1');
$I->getOParl('/organization/referat/1');
