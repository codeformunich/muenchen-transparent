<?php
$I = new OparlTester($scenario);
$I->wantTo('get two oparl:membership object (one with end date and one without)');
$I->sendGET('/membership_referat/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/body/1/membership_referat/",
  "type": "https://oparl.org/schema/1.0/Member­ship",
  "organization": "http://localhost:8080/oparl/v1.0/body/1/organization_referat/",
  "person": "http://localhost:8080/oparl/v1.0/body/3/person/",
  "role": "Referent"
}
');
