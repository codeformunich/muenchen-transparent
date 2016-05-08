<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:membership objects (one fraktion and one referat)');
$I->sendGET('/membership/fraktion/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/membership/fraktion/1",
  "type": "https://oparl.org/schema/1.0/Membership",
  "organization": "http://localhost:8080/oparl/v1.0/organization/fraktion/1",
  "person": "http://localhost:8080/oparl/v1.0/person/1",
  "role": "Mitglied",
  "startDate": "2000-01-01",
  "endDate": "2004-01-01"
}
');
$I->sendGET('/membership/referat/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/membership/referat/1",
  "type": "https://oparl.org/schema/1.0/Membership",
  "organization": "http://localhost:8080/oparl/v1.0/organization/referat/1",
  "person": "http://localhost:8080/oparl/v1.0/person/3",
  "role": "Referent"
}
');
$I->sendGET('/membership/gremium/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/membership/gremium/1",
  "type": "https://oparl.org/schema/1.0/Membership",
  "organization": "http://localhost:8080/oparl/v1.0/organization/gremium/2",
  "person": "http://localhost:8080/oparl/v1.0/person/4",
  "role": "Mitglied",
  "startDate": "2016-05-01",
  "endDate": "2016-05-02"
}
');
