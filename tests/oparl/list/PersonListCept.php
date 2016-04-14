<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:person');
$I->sendGET('/body/0/list/person');
$I->seeOparl('
{
  "items": [
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
      "muenchen-transparent:elected": "2014-05-01",
      "muenchen-transparent:dateOfBirth": "1971-05-31",
      "muenchen-transparent:beruf": "Stadtrat",
      "muenchen-transparent:bio": "Geboren am 31.05.1971 um 18:09:45\n\nQuery: `SELECT FROM_UNIXTIME(avg(unix_timestamp(geburtstag))) FROM stadtraetInnen WHERE geburtstag`",
      "muenchen-transparent:website": "https://example.com",
      "muenchen-transparent:twitter": "@StadtratmitallenEigenschaften",
      "muenchen-transparent:facebook": "StadtratmitallenEigenschaften_1123410",
      "muenchen-transparent:abgeordnetenwatch": "Stadtrat mit allen Eigenschaften"
    },
    {
      "id": "http://localhost:8080/oparl/v1.0/person/2",
      "type": "https://oparl.org/schema/1.0/Person",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Stadträtin mit möglichst wenigen Eigenschaften",
      "status": "Ehrenamtlicher Stadtrat"
    },
    {
      "id": "http://localhost:8080/oparl/v1.0/person/3",
      "type": "https://oparl.org/schema/1.0/Person",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Referent für Städtische Aufgaben",
      "status": "Berufsmäßiger Stadtrat"
    }
  ],
  "itemsPerPage": 100,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/person",
  "numberOfPages": 1
}
');
$I->sendGET('/body/0/list/person?id=2');
$I->seeOparl('
{
  "items": [
    {
      "id": "http://localhost:8080/oparl/v1.0/person/3",
      "type": "https://oparl.org/schema/1.0/Person",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Referent für Städtische Aufgaben",
      "status": "Berufsmäßiger Stadtrat"
    }
  ],
  "itemsPerPage": 100,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/person",
  "numberOfPages": 1
}
');
