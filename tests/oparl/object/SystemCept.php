<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:system');
$I->getOParl('/');
