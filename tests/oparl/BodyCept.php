<?php
$I = new OparlTester($scenario);
$I->wantTo('get two oparl:body objects (the Stadtrat and one BA)');
$I->sendGET('/body/0');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/body/0",
  "type": "https://oparl.org/schema/1.0/Body",
  "system": "http://localhost:8080/oparl/v1.0",
  "contactEmail": "info@muenchen-transparent.de",
  "contactName": "München Transparent",
  "name": "Stadrat der Landeshauptstadt München",
  "shortName": "Stadtrat",
  "website": "http://www.muenchen.de/",
  "organization": "http://localhost:8080/oparl/v1.0/body/0/list/organization",
  "person": "http://localhost:8080/oparl/v1.0/body/0/list/person",
  "meeting": "http://localhost:8080/oparl/v1.0/body/0/list/meeting",
  "paper": "http://localhost:8080/oparl/v1.0/body/0/list/paper",
  "terms": "http://localhost:8080/oparl/v1.0/body/0/list/term"
}
');
$I->sendGET('/body/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/body/1",
  "type": "https://oparl.org/schema/1.0/Body",
  "system": "http://localhost:8080/oparl/v1.0",
  "contactEmail": "info@muenchen-transparent.de",
  "contactName": "München Transparent",
  "name": "Bezirksausschuss 1: BA mit Ausschuss mit Termin",
  "shortName": "BA 1",
  "website": "http://localhost:8080/bezirksausschuss/1_BA+mit+Ausschuss+mit+Termin",
  "organization": "http://localhost:8080/oparl/v1.0/body/1/list/organization",
  "person": "http://localhost:8080/oparl/v1.0/body/1/list/person",
  "meeting": "http://localhost:8080/oparl/v1.0/body/1/list/meeting",
  "paper": "http://localhost:8080/oparl/v1.0/body/1/list/paper",
  "terms": "http://localhost:8080/oparl/v1.0/body/1/list/term"
}
');
