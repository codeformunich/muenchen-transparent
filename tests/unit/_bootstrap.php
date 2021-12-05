<?php
// Here you can initialize variables that will be available to your tests

// Required for yii's error reporting


require_once(dirname(__FILE__) . "/../../vendor/autoload.php");

$yii    = dirname(__FILE__) . '/../../vendor/yiisoft/yii/framework/yii.php';
$config = dirname(__FILE__) . '/../../protected/config/main-yii-codeception.php';

require_once($yii);

Yii::setPathOfAlias("composer", __DIR__ . "/../../vendor/");
Yii::createWebApplication($config);

if (!defined("SERVER_NAME")) define("SERVER_NAME", parse_url(SITE_BASE_URL)['host']);
$_SERVER["SERVER_NAME"] = parse_url(SITE_BASE_URL)['host'];
$_SERVER['REQUEST_URI'] = SITE_BASE_URL;
$_SERVER["SERVER_PROTOCOL"] = "HTTP";
