<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:file objects (one with many attributes, one with few attributes and one Rathausumschau)');
$I->sendGET('/file/7');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/file/7",
  "type": "https://oparl.org/schema/1.0/File",
  "name": "Dokument mit vielen Eigenschaften",
  "muenchenTransparent:orignalAccessUrl": "http://www.ris-muenchen.de/RII/RII/7.pdf",
  "fileName": "Dokument mit vielen Eigenschaften.pdf",
  "mimeType": "application/pdf",
  "accessUrl": "http://localhost:8080/dokumente/7.pdf",
  "muenchenTransparent:ocrCreator": "omnipage"
}
');
$I->sendGET('/file/8');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/file/8",
  "type": "https://oparl.org/schema/1.0/File",
  "name": "Dokument (tiff) mit wenig Eigenschaften",
  "muenchenTransparent:orignalAccessUrl": "http://www.ris-muenchen.de/RII/RII/8.tiff",
  "fileName": "Dokument (tiff) mit wenig Eigenschaften.tiff",
  "mimeType": "image/tiff",
  "accessUrl": "http://localhost:8080/dokumente/8.tiff"
}
');
$I->sendGET('/file/9');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0/file/9",
  "type": "https://oparl.org/schema/1.0/File",
  "name": "Rathausumschau",
  "muenchenTransparent:orignalAccessUrl": "http://www.ris-muenchen.dehttp://example.org/rathausumschau/1-rathaus.pdf",
  "fileName": "Rathausumschau.pdf",
  "mimeType": "application/pdf",
  "accessUrl": "http://localhost:8080/dokumente/9.pdf"
}
');
