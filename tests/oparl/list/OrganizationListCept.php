<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:organization');
$I->getOParl('/body/0/list/organization');
$I->getOParl('/body/1/list/organization');
