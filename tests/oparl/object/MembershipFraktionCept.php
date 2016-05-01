<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:membership objects (one with end date and one without)');
$I->sendGET('/membership/fraktion/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/membership/fraktion/1",
  "type": "https://oparl.org/schema/1.0/Membership",
  "organization": "http://localhost:8080/oparl/v1.0/organization/fraktion/1",
  "person": "http://localhost:8080/oparl/v1.0/person/1",
  "role": "Mitglied",
  "startDate": "2000-01-01",
  "votingRight": true,
  "muenchenTransparent:term": "http://localhost:8080/oparl/v1.0/term/2",
  "endDate": "2004-01-01"
}
');
$I->sendGET('/membership/fraktion/2');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/membership/fraktion/2",
  "type": "https://oparl.org/schema/1.0/Membership",
  "organization": "http://localhost:8080/oparl/v1.0/organization/fraktion/1",
  "person": "http://localhost:8080/oparl/v1.0/person/1",
  "role": "Vorsitzender",
  "startDate": "2004-01-01",
  "votingRight": true,
  "muenchenTransparent:term": "http://localhost:8080/oparl/v1.0/term/3"
}
');
