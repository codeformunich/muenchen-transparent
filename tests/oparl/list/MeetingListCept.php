<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:meeting');
$I->getOParl('/body/0/list/meeting');
$I->getOParl('/body/1/list/meeting');

// Check that objects with no organization do not get printed
$I->getOParl('/body/0/list/meeting?id=3');
foreach ($I->getResponseAsTree()->data as $object) {
    $I->assertNotEquals($object->meetingState, "Dateninkonsitent");
}
