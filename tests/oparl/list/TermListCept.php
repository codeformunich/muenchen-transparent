<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:term');
$I->getOParl('/body/0/list/legislativeterm');
// There's no need to check for a second body as the data doesn't depend on the body
