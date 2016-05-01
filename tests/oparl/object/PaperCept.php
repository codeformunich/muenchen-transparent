<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:paper objects (one with all attributes and one with few attributes)');
$I->sendGET('/paper/7');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/paper/7",
  "type": "https://oparl.org/schema/1.0/Paper",
  "body": "http://localhost:8080/oparl/v1.0/body/0",
  "name": "betreff",
  "reference": "",
  "paperType": "Stadtratsantrag",
  "auxiliaryFile": [],
  "underDirectionof": [
    "http://localhost:8080/oparl/v1.0/organization/referat/1"
  ],
  "keyword": []
}
');
$I->sendGET('/paper/8');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/paper/8",
  "type": "https://oparl.org/schema/1.0/Paper",
  "body": "http://localhost:8080/oparl/v1.0/body/1",
  "name": "",
  "reference": "",
  "paperType": "BA-Antrag",
  "auxiliaryFile": [],
  "underDirectionof": [
    "http://localhost:8080/oparl/v1.0/organization/referat/1"
  ],
  "keyword": []
}
');
