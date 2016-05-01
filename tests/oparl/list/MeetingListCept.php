<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:meeting');
$I->sendGET('/body/0/list/meeting');
$I->seeOparl('
{
  "items": [
    {
      "id": "http://localhost:8080/oparl/v1.0/meeting/1",
      "type": "https://oparl.org/schema/1.0/Meeting",
      "name": "Ausschuss mit Terminen",
      "meetingState": "",
      "start": "2016-01-01T09:00:00+01:00",
      "organization": "http://localhost:8080/oparl/v1.0/organization/gremium/1",
      "modified": "2016-01-31T17:27:28+01:00",
      "auxiliaryFile": []
    },
    {
      "id": "http://localhost:8080/oparl/v1.0/meeting/2",
      "type": "https://oparl.org/schema/1.0/Meeting",
      "name": "Ausschuss mit Terminen",
      "meetingState": "",
      "start": "2016-02-01T09:00:00+01:00",
      "organization": "http://localhost:8080/oparl/v1.0/organization/gremium/1",
      "modified": "2016-01-31T17:27:28+01:00",
      "auxiliaryFile": []
    },
    {
      "id": "http://localhost:8080/oparl/v1.0/meeting/3",
      "type": "https://oparl.org/schema/1.0/Meeting",
      "name": "Ausschuss mit Terminen",
      "meetingState": "",
      "start": "2015-12-01T09:00:00+01:00",
      "organization": "http://localhost:8080/oparl/v1.0/organization/gremium/1",
      "modified": "2016-01-31T17:27:28+01:00",
      "auxiliaryFile": []
    }
  ],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/meeting",
  "numberOfPages": 2,
  "nextPage": "http://localhost:8080/oparl/v1.0/body/0/list/meeting?id=3"
}
');
$I->sendGET('/body/0/list/meeting?id=3');
$I->seeOparl('
{
  "items": [
    {
      "id": "http://localhost:8080/oparl/v1.0/meeting/4",
      "type": "https://oparl.org/schema/1.0/Meeting",
      "name": "Ausschuss mit Terminen",
      "meetingState": "",
      "start": "2016-04-12T00:00:00+02:00",
      "organization": "http://localhost:8080/oparl/v1.0/organization/gremium/1",
      "modified": "2016-04-23T18:27:45+02:00",
      "auxiliaryFile": [
        "http://localhost:8080/oparl/v1.0/file/7"
      ]
    }
  ],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/meeting",
  "numberOfPages": 2
}
');
$I->sendGET('/body/1/list/meeting');
$I->seeOparl('
{
  "items": [],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/1/list/meeting",
  "numberOfPages": 0
}
');
