<?php
$I = new OparlTester($scenario);
$I->wantTo('get two oparl:membership object (one with end date and one without)');
$I->sendGET('/membership_fraktion/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/body/1/membership_fraktion/",
  "type": "https://oparl.org/schema/1.0/Member­ship",
  "organization": "http://localhost:8080/oparl/v1.0/body/1/organization_fraktion/",
  "person": "http://localhost:8080/oparl/v1.0/body/1/person/",
  "role": "Mitglied",
  "startDate": "2000-01-01",
  "votingRight": true,
  "muenchen-transparent:term": "http://localhost:8080/oparl/v1.0/body/2/term/",
  "endDate": "2004-01-01"
}
');
$I->sendGET('/membership_fraktion/2');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/body/2/membership_fraktion/",
  "type": "https://oparl.org/schema/1.0/Member­ship",
  "organization": "http://localhost:8080/oparl/v1.0/body/1/organization_fraktion/",
  "person": "http://localhost:8080/oparl/v1.0/body/1/person/",
  "role": "Vorsitzender",
  "startDate": "2004-01-01",
  "votingRight": true,
  "muenchen-transparent:term": "http://localhost:8080/oparl/v1.0/body/3/term/"
}
');
