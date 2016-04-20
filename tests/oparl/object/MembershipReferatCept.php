<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:membership objects (one with end date and one without)');
$I->sendGET('/membership_referat/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/membership_referat/1",
  "type": "https://oparl.org/schema/1.0/Membership",
  "organization": "http://localhost:8080/oparl/v1.0/organization_referat/1",
  "person": "http://localhost:8080/oparl/v1.0/person/3",
  "role": "Referent"
}
');
