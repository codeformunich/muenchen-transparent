<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:file objects (one with many attributes, one with few attributes and one Rathausumschau)');
$I->getOParl('/file/7');
$I->getOParl('/file/8');
$I->getOParl('/file/9');
