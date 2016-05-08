<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:term objects (the unknown term and the 1996-2002 one)');
$I->sendGET('/legislativeterm/0');
$I->seeOparl('
{
  "type": "https://oparl.org/schema/1.0/LegislativeTerm",
  "name": "Unbekannt",
  "startDate": "0000-00-00",
  "endDate": "0000-00-00",
  "id": "http://localhost:8080/oparl/v1.0/legislativeterm/0"
}
');
$I->sendGET('/legislativeterm/1');
$I->seeOparl('
{
  "type": "https://oparl.org/schema/1.0/LegislativeTerm",
  "name": "1996-2002",
  "startDate": "1996-12-03",
  "endDate": "2002-12-03",
  "id": "http://localhost:8080/oparl/v1.0/legislativeterm/1"
}
');
