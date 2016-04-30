<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:paper');
$I->sendGET('/body/0/list/paper');
$I->seeOparl('
{
  "items": [
    {
      "id": "http://localhost:8080/oparl/v1.0/paper/1",
      "type": "https://oparl.org/schema/1.0/Paper",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Antrag ohne Vorgang",
      "reference": "",
      "paperType": "Stadtratsantrag",
      "auxiliaryFile": [],
      "underDirectionof": [
        "http://localhost:8080/oparl/v1.0/organization_referat/1"
      ],
      "keyword": []
    },
    {
      "id": "http://localhost:8080/oparl/v1.0/paper/2",
      "type": "https://oparl.org/schema/1.0/Paper",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Antrag mit verwandten Seiten",
      "reference": "",
      "paperType": "Stadtratsantrag",
      "auxiliaryFile": [
        "http://localhost:8080/oparl/v1.0/file/2"
      ],
      "underDirectionof": [
        "http://localhost:8080/oparl/v1.0/organization_referat/1"
      ],
      "keyword": [],
      "relatedPaper": [
        "http://localhost:8080/oparl/v1.0/paper/2",
        "http://localhost:8080/oparl/v1.0/paper/3"
      ]
    },
    {
      "id": "http://localhost:8080/oparl/v1.0/paper/3",
      "type": "https://oparl.org/schema/1.0/Paper",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Ein verwandter Antrag",
      "reference": "",
      "paperType": "Stadtratsantrag",
      "auxiliaryFile": [
        "http://localhost:8080/oparl/v1.0/file/1"
      ],
      "underDirectionof": [
        "http://localhost:8080/oparl/v1.0/organization_referat/1"
      ],
      "keyword": [],
      "relatedPaper": [
        "http://localhost:8080/oparl/v1.0/paper/2",
        "http://localhost:8080/oparl/v1.0/paper/3"
      ]
    }
  ],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/paper",
  "numberOfPages": 3,
  "nextPage": "http://localhost:8080/oparl/v1.0/body/0/list/paper?id=3"
}
');
$I->sendGET('/body/0/list/paper?id=3');
$I->seeOparl('
{
  "items": [
    {
      "id": "http://localhost:8080/oparl/v1.0/paper/4",
      "type": "https://oparl.org/schema/1.0/Paper",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Antrag mit mehreren Dokumenten",
      "reference": "",
      "paperType": "Stadtratsantrag",
      "auxiliaryFile": [
        "http://localhost:8080/oparl/v1.0/file/3",
        "http://localhost:8080/oparl/v1.0/file/4"
      ],
      "underDirectionof": [
        "http://localhost:8080/oparl/v1.0/organization_referat/1"
      ],
      "keyword": []
    },
    {
      "id": "http://localhost:8080/oparl/v1.0/paper/5",
      "type": "https://oparl.org/schema/1.0/Paper",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Ein Antrag mit einem Dokument",
      "reference": "",
      "paperType": "Stadtratsantrag",
      "auxiliaryFile": [
        "http://localhost:8080/oparl/v1.0/file/5"
      ],
      "underDirectionof": [
        "http://localhost:8080/oparl/v1.0/organization_referat/1"
      ],
      "keyword": []
    },
    {
      "id": "http://localhost:8080/oparl/v1.0/paper/6",
      "type": "https://oparl.org/schema/1.0/Paper",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Antrag mit Dokument mit vielen Eigenschaften",
      "reference": "",
      "paperType": "Stadtratsantrag",
      "auxiliaryFile": [
        "http://localhost:8080/oparl/v1.0/file/7"
      ],
      "underDirectionof": [
        "http://localhost:8080/oparl/v1.0/organization_referat/1"
      ],
      "keyword": []
    }
  ],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/paper",
  "numberOfPages": 3,
  "nextPage": "http://localhost:8080/oparl/v1.0/body/0/list/paper?id=6"
}
');
$I->sendGET('/body/1/list/paper');
$I->seeOparl('
{
  "items": [
    {
      "id": "http://localhost:8080/oparl/v1.0/paper/8",
      "type": "https://oparl.org/schema/1.0/Paper",
      "body": "http://localhost:8080/oparl/v1.0/body/1",
      "name": "",
      "reference": "",
      "paperType": "BA-Antrag",
      "auxiliaryFile": [],
      "underDirectionof": [
        "http://localhost:8080/oparl/v1.0/organization_referat/1"
      ],
      "keyword": []
    }
  ],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/1/list/paper",
  "numberOfPages": 1
}
');
