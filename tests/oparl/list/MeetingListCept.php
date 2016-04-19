<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:meeting');
$I->sendGET('/body/0/list/meeting');
$I->seeOparl('
{
  "items": [],
  "itemsPerPage": 100,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/meeting",
  "numberOfPages": 1
}
');
$I->sendGET('/body/1/list/meeting');
$I->seeOparl('
{
  "items": [],
  "itemsPerPage": 100,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/1/list/meeting",
  "numberOfPages": 1
}
');
