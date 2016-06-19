<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:paper');
$I->getOParl('/body/0/list/paper');
$I->getOParl('/body/0/list/paper?id=3');
$I->getOParl('/body/1/list/paper');
