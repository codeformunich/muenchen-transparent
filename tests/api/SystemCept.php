<?php 
$I = new ApiTester($scenario);
$I->wantTo('get the oparl:system object');
$I->sendGET('/');
$I->seeOparl('{
  "id": "http://localhost:8080/oparl/v1.0",
  "type": "https://oparl.org/schema/1.0/System",
  "oparlVersion": "https://oparl.org/specs/1.0/",
  "otherOparlVersions": [],
  "body": "http://localhost:8080/oparl/v1.0/bodies",
  "name": "München Transparent",
  "contactEmail": "info@muenchen-transparent.de",
  "contactName": "München Transparent",
  "website": "http://localhost:8080",
  "vendor": "https://github.com/codeformunich/Muenchen-Transparent",
  "product": "https://github.com/codeformunich/Muenchen-Transparent"
}');
