<?php

// Helper file for the yii1-codeception bridge

defined('YII_DEBUG') or define('YII_DEBUG',true);

// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

ini_set('include_path', ini_get('include_path') . ":" . dirname(__FILE__) . "/../libraries/");
if (!file_exists(dirname(__FILE__) . "/../vendor/autoload.php")) {
    die("Installation noch nicht vollst&auml;ndig: bitte f&uuml;hre 'composer install' aus. Falls composer nicht installiert ist, siehe: http://getcomposer.org/");
}
require_once(dirname(__FILE__) . "/../vendor/autoload.php");

$yii    = dirname(__FILE__) . '/../vendor/yiisoft/yii/framework/yii.php';
$yiit   = dirname(__FILE__) . '/../vendor/codeception/yii-bridge/yiit.php';
$config = dirname(__FILE__) . '/../protected/config/main-yii-codeception.php';

require_once($yii);
require_once($yiit);

return array(
    'class' => 'CWebApplication',
    'config' => $config,
);
