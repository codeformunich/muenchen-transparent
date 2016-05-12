<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:file objects (one with many attributes, one with few attributes and one Rathausumschau)');
$I->sendGET('/file/7');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/file/7",
  "type": "https://oparl.org/schema/1.0/File",
  "name": "Dokument (pdf) mit vielen Eigenschaften",
  "accessUrl": "http://localhost:8080/fileaccess/access/7",
  "downloadUrl": "http://localhost:8080/fileaccess/download/7",
  "fileName": "7 - Dokument (pdf) mit vielen Eigenschaften.pdf",
  "created": "2016-05-02T19:53:08+02:00",
  "modified": "2016-05-08T22:57:23+02:00",
  "mimeType": "application/pdf",
  "meeting": [
    "http://localhost:8080/oparl/v1.0/meeting/4"
  ],
  "paper": [
    "http://localhost:8080/oparl/v1.0/paper/6"
  ],
  "muenchenTransparent:ocrCreator": "omnipage"
}
');
$I->sendGET('/file/8');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/file/8",
  "type": "https://oparl.org/schema/1.0/File",
  "name": "Dokument (tiff) mit wenig Eigenschaften",
  "accessUrl": "http://localhost:8080/fileaccess/access/8",
  "downloadUrl": "http://localhost:8080/fileaccess/download/8",
  "fileName": "8 - Dokument (tiff) mit wenig Eigenschaften.pdf",
  "created": "2016-05-02T19:53:08+02:00",
  "modified": "2016-05-08T22:57:28+02:00",
  "mimeType": "image/tiff"
}
');
$I->sendGET('/file/9');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/file/9",
  "type": "https://oparl.org/schema/1.0/File",
  "name": "Rathausumschau",
  "accessUrl": "http://localhost:8080/fileaccess/access/9",
  "downloadUrl": "http://localhost:8080/fileaccess/download/9",
  "fileName": "9 - Rathausumschau.pdf",
  "created": "2016-05-02T19:53:08+02:00",
  "modified": "2016-05-09T18:09:15+02:00",
  "mimeType": "application/pdf"
}
');
