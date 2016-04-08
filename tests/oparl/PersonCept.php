<?php 
$I = new OparlTester($scenario);
$I->wantTo('get two oparl:person objects (one with all attributes and one with few attributes)');
$I->sendGET('/person/1');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/body/1/person/1",
  "type": "https://oparl.org/schema/1.0/Person",
  "body": "http://localhost:8080/oparl/v1.0/body/1",
  "name": "Stadtrat mit allen Eigenschaften",
  "gender": "male",
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
}
');
$I->sendGET('/person/2');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/body/0/person/2",
  "type": "https://oparl.org/schema/1.0/Person",
  "body": "http://localhost:8080/oparl/v1.0/body/0",
  "name": "Stadträtin mit möglichst wenigen Eigenschaften"
}
');
