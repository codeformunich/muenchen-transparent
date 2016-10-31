<?php

// Helper for the yii1-codeception bridge
// See the yii-codeception documentation for more information

define("SITE_BASE_URL", 'http://yii-codeception.test');

$x = require_once("main-test.php");

$x["components"]['request'] = [
    'class' => 'CodeceptionHttpRequest'
];

return $x;

