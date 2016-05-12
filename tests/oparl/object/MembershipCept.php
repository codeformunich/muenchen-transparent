<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:membership objects (one fraktion and one referat)');
$I->sendGET('/membership/fraktion/1');
$I->seeOparl('
{
    "id": "http://localhost:8080/oparl/v1.0/organization/gremium/1",
    "type": "https://oparl.org/schema/1.0/Organization",
    "body": "http://localhost:8080/oparl/v1.0/body/1",
    "name": "Ausschuss mit Terminen",
    "shortName": "Ausschuss mit Terminen",
    "membership": [],
    "classification": "BA-Gremium",
    "created": "2016-05-02T19:53:08+02:00",
    "modified": "2016-05-02T19:53:09+02:00",
    "meetings": [
        "http://localhost:8080/oparl/v1.0/meeting/1",
        "http://localhost:8080/oparl/v1.0/meeting/2",
        "http://localhost:8080/oparl/v1.0/meeting/3",
        "http://localhost:8080/oparl/v1.0/meeting/4"
    ]
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
