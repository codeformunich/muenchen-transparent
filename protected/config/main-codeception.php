<?php

define("SITE_BASE_URL", "http://localhost:8080");

if (!defined("SITE_CALL_MODE")) define("SITE_CALL_MODE", "web");

define("RIS_DATA_DIR", "/data/ris3-data/");
define("RIS_OMNIPAGE_DIR", "/data/nuance/");
define("PATH_IDENTIFY", "/usr/bin/identify");
define("PATH_CONVERT", "/usr/bin/convert");
define("PATH_TESSERACT", "/usr/local/bin/tesseract");
define("PATH_JAVA", "/usr/local/java/bin/java");
define("PATH_PDFTOTEXT", "/usr/bin/pdftotext");
define("PATH_PDFBOX", RIS_DATA_DIR . "pdfbox-app-1.8.10.jar");
define("PATH_PDFINFO", "/usr/bin/pdfinfo");

define("PATH_PDF", RIS_DATA_DIR . "data/pdf/");
define("PATH_PDF_RU", RIS_DATA_DIR . "data/ru-pdf/");
define("TMP_PATH", "/tmp/");
define("LOG_PATH", RIS_DATA_DIR . "logs/");
define("RU_PDF_PATH", RIS_DATA_DIR . "data/ru-pdf/");
define("OMNIPAGE_PDF_DIR", RIS_OMNIPAGE_DIR . "ocr-todo/");
define("OMNIPAGE_DST_DIR", RIS_OMNIPAGE_DIR . "ocr-dst/");
define("OMNIPAGE_IMPORTED_DIR", RIS_OMNIPAGE_DIR . "ocr-imported/");
define("TILE_CACHE_DIR", RIS_DATA_DIR . "tile-cache/tiles/");
define("EMAIL_LOG_FILE", "/tmp/email.log");

// Konstanten, die das RIS betreffen
define("RIS_URL_PREFIX", "http://localhost:8080"); // Zum Testen des Proxys
define("RIS_BASE_URL", "https://www.ris-muenchen.de/RII/RII/");
define("RIS_BA_BASE_URL",  "https://www.ris-muenchen.de/RII/BA-RII/");
define("RATHAUSUMSCHAU_WEBSITE",  "http://www.muenchen.de/rathaus/Stadtinfos/Presse-Service.html");
define("OPARL_10_ROOT", SITE_BASE_URL . '/oparl/v1.0');

define("DOCUMENT_DATE_ACCURATE_SINCE", 1388530800); // 1. Januar 2014
define("DOCUMENT_DATE_UNKNOWN_BEFORE", 1212271200); // 1. Juni 2008

define("OPARL_10_ITEMS_PER_PAGE", 3); // Macht das Testen einfacher

define("SEED_KEY", "RANDOMKEY");
define("MAILGUN_API_KEY", "");
define("MAILGUN_DOMAIN", "");
define("NO_ERROR_MAIL", true);

ini_set("memory_limit", "256M");
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");
define("DEFAULT_TIMEZONE", "Europe/Berlin");
date_default_timezone_set(DEFAULT_TIMEZONE);
ini_set('mbstring.substitute_character', "none");
setlocale(LC_TIME, "de_DE.UTF-8");
setlocale(LC_NUMERIC, "C"); // Scheint in manchen Umgebungen (HHVM?) sonst bei de_DE Probleme mit FloatVal zu machen

require_once(__DIR__ . "/urls.php");

$GLOBALS["SOLR_CONFIG"] = null;


function ris_intern_address2geo($land, $plz, $ort, $strasse)
{
    return ["lon" => 0, "lat" => 0];
}


/**
 * @param string $url_to_read
 * @param string $username
 * @param string $password
 * @param int $timeout
 * @return string
 * @throws Exception
 */
function ris_download_string($url_to_read, $username = "", $password = "", $timeout = 30)
{
    $ch = curl_init();

    if ($username != "" || $password != "") curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

    curl_setopt($ch, CURLOPT_URL, $url_to_read);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //curl_setopt($ch, CURLOPT_PROXY, RISTools::STD_PROXY);
    $data = curl_exec($ch);

    $info = curl_getinfo($ch);
    if ($info["http_code"] != 200) throw new Exception("Not Found");

    curl_close($ch);

    return $data;
}


/**
 * @param Antrag $referenz
 * @param Antrag $antrag
 * @return bool
 */
function ris_intern_antrag_ist_relevant_mlt($referenz, $antrag)
{
    return true;
}

function ris_intern_html_extra_headers()
{
    return '';
}


// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return [
    'basePath'       => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name'           => 'München Transparent',

    // preloading 'log' component
    'preload'        => ['log'],

    // autoloading model and component classes
    'import'         => [
        'application.models.*',
        'application.components.*',
        'application.RISParser.*',
    ],

    'onBeginRequest' => create_function('$event', 'if (SITE_CALL_MODE == "web") return ob_start("ob_gzhandler");'),
    'onEndRequest'   => create_function('$event', 'if (SITE_CALL_MODE == "web" && ob_get_level() > 0) return ob_end_flush();'),

    'timeZone' => 'Europe/Berlin',

    'modules'        => [
        // uncomment the following to enable the Gii tool
        'gii' => [
            'class'    => 'system.gii.GiiModule',
            'password' => 'RANDOMKEY',
            // If removed, Gii defaults to localhost only. Edit carefully to taste.
            //'ipFilters' => array('*', '::1'),
        ],
    ],

    // application components
    'components'     => [
        'cache'        => [
            'class' => 'system.caching.CFileCache',
        ],
        'urlManager'   => [
            'urlFormat'      => 'path',
            'showScriptName' => false,
            'rules'          => $GLOBALS["RIS_URL_RULES"],
        ],
        'db'           => [
            'connectionString'      => 'mysql:host=127.0.0.1;dbname=ristest',
            'emulatePrepare'        => true,
            'username'              => 'travis',
            'password'              => '',
            'charset'               => 'utf8mb4',
            'queryCacheID'          => 'apcCache',
            'schemaCachingDuration' => 3600,
            'initSQLs'              => ['SET time_zone = "' . DEFAULT_TIMEZONE . '"'],
        ],
        'errorHandler' => [
            // use 'site/error' action to display errors
            'errorAction' => 'index/error',
        ],
        'log'          => [
            'class'  => 'CLogRouter',
            'routes' => [
                [
                    'class'  => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ],
                /*
                array(
                    'class' => 'CWebLogRoute',
                ),
                */
            ],
        ],
    ],

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params'         => [
        // this is used in contact page
        'adminEmail'          => 'info@muenchen-transparent.de',
        'adminEmailName'      => "München Transparent",
        'skobblerKey'         => 'KEY',
        'baseURL'             => SITE_BASE_URL,
        'debug_log'           => true,
        'projectTitle'        => 'München Transparent',
        'startseiten_warnung' => '',
    ],
];
