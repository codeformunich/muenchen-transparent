<?php
define("DOCUMENT_DATE_ACCURATE_SINCE", 1388530800); // 1. Januar 2014
define("DOCUMENT_DATE_UNKNOWN_BEFORE", 1212271200); // 1. Juni 2008

// PHP internals
ini_set("memory_limit", "256M");
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
define("DEFAULT_TIMEZONE", "Europe/Berlin");
date_default_timezone_set(DEFAULT_TIMEZONE);
ini_set('mbstring.substitute_character', "none");
setlocale(LC_TIME, "de_DE.UTF-8");
setlocale(LC_NUMERIC, "C"); // Scheint in manchen Umgebungen (HHVM?) sonst bei de_DE Probleme mit FloatVal zu machen
