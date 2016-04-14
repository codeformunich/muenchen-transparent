<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:organization objects (a Fraktion, a BA-Gremium and a Referat)');
$I->sendGET('/organization_fraktion/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/organization_fraktion/1",
  "type": "https://oparl.org/schema/1.0/Organ­ization",
  "body": "http://localhost:8080/oparl/v1.0/body/1",
  "name": "Fraktion der Politiker",
  "shortName": "Fraktion der Politiker",
  "meeting": [],
  "membership": [],
  "classification": "Fraktion"
}
');
$I->sendGET('/organization_gremium/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/organization_gremium/1",
  "type": "https://oparl.org/schema/1.0/Organ­ization",
  "body": "http://localhost:8080/oparl/v1.0/body/1",
  "name": "Ausschuss mit Terminen",
  "shortName": "Ausschuss mit Terminen",
  "meeting": [],
  "membership": [],
  "classification": "BA-Gremium"
}
');
$I->sendGET('/organization_referat/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/organization_referat/1",
  "type": "https://oparl.org/schema/1.0/Organ­ization",
  "body": "http://localhost:8080/oparl/v1.0/body/0",
  "name": "Referat für städtische Aufgaben",
  "shortName": "Referat für städtische Aufgaben",
  "meeting": [],
  "membership": [],
  "classification": "Referat"
}
');
