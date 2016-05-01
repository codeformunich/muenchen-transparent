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
  "meeting": [],
  "membership": [],
  "classification": "Fraktion"
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
  "meeting": [],
  "membership": [],
  "classification": "BA-Gremium"
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
  "meeting": [],
  "membership": [],
  "classification": "Referat"
}
');
