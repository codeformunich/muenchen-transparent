<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:paper');
$I->sendGET('/body/0/list/paper');
$I->seeOparl('
{
  "items": [],
  "itemsPerPage": 100,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/paper",
  "numberOfPages": 1
}
');
$I->sendGET('/body/1/list/paper');
$I->seeOparl('
{
  "items": [],
  "itemsPerPage": 100,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/1/list/paper",
  "numberOfPages": 1
}
');
