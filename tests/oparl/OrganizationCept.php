<?php 
$I = new OparlTester($scenario);
$I->wantTo('get two oparl:organization objects (a Gremium and a Fraktion)');
$I->sendGET('/fraktion/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/body/1/fraktion/1",
  "type": "https://oparl.org/schema/1.0/Organ­iz­a­tion",
  "body": "http://localhost:8080/oparl/v1.0/body/1",
  "name": "Fraktion der Politiker",
  "shortName": "Fraktion der Politiker",
  "meeting": [],
  "membership": [],
  "classification": "Fraktion"
}
');
$I->sendGET('/gremium/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/body/1/gremium/1",
  "type": "https://oparl.org/schema/1.0/Organ­iz­a­tion",
  "body": "http://localhost:8080/oparl/v1.0/body/1",
  "name": "Ausschuss mit Terminen",
  "shortName": "Ausschuss mit Terminen",
  "meeting": [],
  "membership": [],
  "classification": "Ausschuss"
}'
);
