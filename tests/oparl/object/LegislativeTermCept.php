<?php
$I = new OparlTester($scenario);
$I->wantTo('validate oparl:legislativeTerm objects (the unknown term and the 1996-2002 one)');
$I->getOParl('/legislativeterm/0');
$I->getOParl('/legislativeterm/1');
