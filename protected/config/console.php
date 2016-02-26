<?php

$_SERVER["HTTP_HOST"] = "www.muenchen-transparent.de";
if (count($_SERVER["argv"]) > 2 && $_SERVER["argv"][2] == "cron") define("SITE_CALL_MODE", "cron");
else define("SITE_CALL_MODE", "shell");

return require dirname(__FILE__).DIRECTORY_SEPARATOR."main.php";
