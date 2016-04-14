<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:system objects');
$I->sendGET('/');
$I->seeOparl('
{
  "id": "http://localhost:8080/oparl/v1.0",
  "type": "https://oparl.org/schema/1.0/System",
  "oparlVersion": "https://oparl.org/specs/1.0/",
  "otherOparlVersions": [],
  "body": "http://localhost:8080/oparl/v1.0/list/body",
  "name": "München Transparent",
  "contactEmail": "info@muenchen-transparent.de",
  "contactName": "München Transparent",
  "website": "http://localhost:8080",
  "vendor": "https://github.com/codeformunich/Muenchen-Transparent",
  "product": "https://github.com/codeformunich/Muenchen-Transparent"
}
');
