<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:person');
$I->getOParl('/body/0/list/person');
$I->getOParl('/body/1/list/person');
