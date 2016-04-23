<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:person objects (one with all attributes, one with few attributes and one Referent)');
$I->sendGET('/person/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/person/1",
  "type": "https://oparl.org/schema/1.0/Person",
  "body": "http://localhost:8080/oparl/v1.0/body/1",
  "name": "Stadtrat mit allen Eigenschaften",
  "gender": "male",
  "status": "Ehrenamtlicher Stadtrat",
  "life": "„Bürgernahe Steuersenkungen für Sicherheit und Freiheit“",
  "lifeSource": "~",
  "email": "meine.email@gmail.com",
  "muenchenTransparent:elected": "2014-05-01",
  "muenchenTransparent:dateOfBirth": "1971-05-31",
  "muenchenTransparent:beruf": "Stadtrat",
  "muenchenTransparent:bio": "Geboren am 31.05.1971 um 18:09:45\n\nQuery: `SELECT FROM_UNIXTIME(avg(unix_timestamp(geburtstag))) FROM stadtraetInnen WHERE geburtstag`",
  "muenchenTransparent:website": "https://example.com",
  "muenchenTransparent:twitter": "@StadtratmitallenEigenschaften",
  "muenchenTransparent:facebook": "StadtratmitallenEigenschaften_1123410",
  "muenchenTransparent:abgeordnetenwatch": "Stadtrat mit allen Eigenschaften"
}
');
$I->sendGET('/person/2');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/person/2",
  "type": "https://oparl.org/schema/1.0/Person",
  "body": "http://localhost:8080/oparl/v1.0/body/0",
  "name": "Stadträtin mit möglichst wenigen Eigenschaften",
  "status": "Ehrenamtlicher Stadtrat"
}
');
$I->sendGET('/person/3');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/person/3",
  "type": "https://oparl.org/schema/1.0/Person",
  "body": "http://localhost:8080/oparl/v1.0/body/0",
  "name": "Referent für Städtische Aufgaben",
  "status": "Berufsmäßiger Stadtrat"
}
');
