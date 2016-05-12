<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:organization objects (one "Fraktion", one "BA-Gremium" and one "Referat")');
$I->sendGET('/organization/fraktion/1');
$I->seeOparl('
{
    "id": "http://localhost:8080/oparl/v1.0/organization/fraktion/1",
    "type": "https://oparl.org/schema/1.0/Organization",
    "body": "http://localhost:8080/oparl/v1.0/body/1",
    "name": "Fraktion der Politiker",
    "shortName": "Fraktion der Politiker",
    "membership": [
        "http://localhost:8080/oparl/v1.0/membership/fraktion/1",
        "http://localhost:8080/oparl/v1.0/membership/fraktion/2"
    ],
    "classification": "Fraktion",
    "created": "2016-05-02T19:53:08+02:00",
    "modified": "2016-05-02T19:53:08+02:00"
}
');
$I->sendGET('/organization/gremium/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/organization/gremium/1",
  "type": "https://oparl.org/schema/1.0/Organization",
  "body": "http://localhost:8080/oparl/v1.0/body/1",
  "name": "Ausschuss mit Terminen",
  "shortName": "Ausschuss mit Terminen",
  "membership": [],
  "classification": "BA-Gremium",
  "meetings": [
    "http://localhost:8080/oparl/v1.0/meeting/1",
    "http://localhost:8080/oparl/v1.0/meeting/2",
    "http://localhost:8080/oparl/v1.0/meeting/3",
    "http://localhost:8080/oparl/v1.0/meeting/4"
  ]
}
');
$I->sendGET('/organization/referat/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/organization/referat/1",
  "type": "https://oparl.org/schema/1.0/Organization",
  "body": "http://localhost:8080/oparl/v1.0/body/0",
  "name": "Referat für städtische Aufgaben",
  "shortName": "Referat für städtische Aufgaben",
  "membership": [
    "http://localhost:8080/oparl/v1.0/membership/referat/1"
  ],
  "classification": "Referat"
}
');
