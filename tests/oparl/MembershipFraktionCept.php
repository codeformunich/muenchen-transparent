<?php
$I = new OparlTester($scenario);
$I->wantTo('get two oparl:membership object (one with end date and one without)');
$I->sendGET('/membership_fraktion/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/membership_fraktion/1",
  "type": "https://oparl.org/schema/1.0/Member­ship",
  "organization": "http://localhost:8080/oparl/v1.0/organization_fraktion/1",
  "person": "http://localhost:8080/oparl/v1.0/person/1",
  "role": "Mitglied",
  "startDate": "2000-01-01",
  "votingRight": true,
  "muenchen-transparent:term": "http://localhost:8080/oparl/v1.0/term/2",
  "endDate": "2004-01-01"
}
');
$I->sendGET('/membership_fraktion/2');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/membership_fraktion/2",
  "type": "https://oparl.org/schema/1.0/Member­ship",
  "organization": "http://localhost:8080/oparl/v1.0/organization_fraktion/1",
  "person": "http://localhost:8080/oparl/v1.0/person/1",
  "role": "Vorsitzender",
  "startDate": "2004-01-01",
  "votingRight": true,
  "muenchen-transparent:term": "http://localhost:8080/oparl/v1.0/term/3"
}
');
