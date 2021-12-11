<?php
$yii    = __DIR__ . '/../../vendor/yiisoft/yii/framework/yii.php';
$config = __DIR__ . '/../../protected/config/main-test.php';

require_once($yii);

Yii::setPathOfAlias("composer", __DIR__ . "/../../vendor/");
Yii::createWebApplication($config);

define("IN_TEST_MODE", true);
