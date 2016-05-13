<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:meeting');
$I->getOParl('/body/0/list/meeting');
$I->getOParl('/body/0/list/meeting?id=3');
$I->getOParl('/body/1/list/meeting');
