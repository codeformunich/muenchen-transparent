<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:organization');
$I->sendGET('/body/0/list/organization');
$I->seeOparl('
{
  "items": [
    {
      "id": "http://localhost:8080/oparl/v1.0/organization_fraktion/2",
      "type": "https://oparl.org/schema/1.0/Organ­ization",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Fraktion des Stadtrat",
      "shortName": "Fraktion des Stadtrat",
      "meeting": [],
      "membership": [],
      "classification": "Fraktion"
    }
  ],
  "itemsPerPage": 100,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/organization",
  "lastPage": "http://localhost:8080/oparl/v1.0/body/0/list/organization",
  "numberOfPages": 1
}
');
