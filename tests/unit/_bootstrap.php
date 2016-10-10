<?php
// Here you can initialize variables that will be available to your tests

// Required for yii's error reporting
if (!defined("SERVER_NAME")) define("SERVER_NAME", SITE_BASE_URL);
$_SERVER["SERVER_NAME"] = SITE_BASE_URL;
$_SERVER['REQUEST_URI'] = SITE_BASE_URL;
$_SERVER["SERVER_PROTOCOL"] = "HTTP";
