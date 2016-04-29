<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:paper');
$I->sendGET('/body/0/list/paper');
$I->seeOparl('
{
  "items": [
    {
      "note:": "not implemented yet"
    },
    {
      "note:": "not implemented yet"
    },
    {
      "note:": "not implemented yet"
    }
  ],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/paper",
  "numberOfPages": 2,
  "nextPage": "http://localhost:8080/oparl/v1.0/body/0/list/paper?id=3"
}
');
$I->sendGET('/body/0/list/paper?id=3');
$I->seeOparl('
{
  "items": [
    {
      "note:": "not implemented yet"
    },
    {
      "note:": "not implemented yet"
    },
    {
      "note:": "not implemented yet"
    }
  ],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/paper",
  "numberOfPages": 2
}
');
$I->sendGET('/body/1/list/paper');
$I->seeOparl('
{
  "items": [],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/1/list/paper",
  "numberOfPages": 0
}
');
