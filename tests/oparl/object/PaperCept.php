<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:paper objects (one with all attributes and one with few attributes)');
$I->getOParl('/paper/7');
$I->getOParl('/paper/8');
