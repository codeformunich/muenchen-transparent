<?php

$_SERVER["HTTP_HOST"] = "www.ratsinformant.de";
if (count($_SERVER["argv"]) > 2 && $_SERVER["argv"][2] == "cron") define("RATSINFORMANT_CALL_MODE", "cron");
else define("RATSINFORMANT_CALL_MODE", "shell");

return require(dirname(__FILE__).DIRECTORY_SEPARATOR."main.php");
