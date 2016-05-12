<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:meeting objects (one with and one with a file)');
$I->sendGET('/meeting/1');
$I->seeOparl('
{
    "id": "http://localhost:8080/oparl/v1.0/meeting/1",
    "type": "https://oparl.org/schema/1.0/Meeting",
    "name": "Ausschuss mit Terminen",
    "meetingState": "",
    "start": "2016-01-01T09:00:00+01:00",
    "organization": "http://localhost:8080/oparl/v1.0/organization/gremium/1",
    "created": "2016-05-02T19:53:09+02:00",
    "modified": "2016-05-02T19:53:10+02:00",
    "auxiliaryFile": []
}
');
$I->sendGET('/meeting/4');
$I->seeOparl('
{
    "id": "http://localhost:8080/oparl/v1.0/meeting/4",
    "type": "https://oparl.org/schema/1.0/Meeting",
    "name": "Ausschuss mit Terminen",
    "meetingState": "",
    "start": "2016-04-12T00:00:00+02:00",
    "organization": "http://localhost:8080/oparl/v1.0/organization/gremium/1",
    "created": "2016-05-02T19:53:09+02:00",
    "modified": "2016-05-02T19:53:10+02:00",
    "auxiliaryFile": [
        "http://localhost:8080/oparl/v1.0/file/7"
    ]
}
');
