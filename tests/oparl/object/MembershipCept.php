<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:membership objects (one fraktion and one referat)');
$I->getOParl('/membership/fraktion/1');
$I->getOParl('/membership/referat/1');
$I->getOParl('/membership/gremium/1');
