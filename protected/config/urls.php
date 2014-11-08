<?php

$GLOBALS["RIS_URL_RULES"] = array(
	RATSINFORMANT_BASE_URL . '/'                                                 => 'index/startseite',
	RATSINFORMANT_BASE_URL . '/ajax-<datum_max:[0-9\-]+>'                        => 'index/antraegeAjax',
	RATSINFORMANT_BASE_URL . '/bezirksausschuss/<ba_nr:\d+>_<ba_name:[^\/]*>'    => 'index/ba',
	RATSINFORMANT_BASE_URL . '/bezirksausschuss/<ba_nr:\d+>'                     => 'index/ba',
	RATSINFORMANT_BASE_URL . '/dokumente/<titel:[\s\S]+>'                        => 'index/dokumente',
	RATSINFORMANT_BASE_URL . '/stadtraetIn/<id:\d+>_<name:[^\/]*>'               => 'index/stadtraetIn',
	RATSINFORMANT_BASE_URL . '/stadtraetIn/<id:\d+>'                             => 'index/stadtraetIn',
	RATSINFORMANT_BASE_URL . '/tiles/<width:\d+>/<zoom:\d+>/<x:\d+>/<y:\d+>.png' => 'index/tileCache',
	RATSINFORMANT_BASE_URL . '/admin/'                                           => 'admin/index',
	RATSINFORMANT_BASE_URL . '/benachrichtigungen'                               => 'benachrichtigungen/index',
	RATSINFORMANT_BASE_URL . '/benachrichtigungen/alleFeed/<code:[0-9\-a-z]+>'   => 'benachrichtigungen/alleFeed',
	RATSINFORMANT_BASE_URL . '/termine/<termin_id:\d+>'                          => 'termine/anzeigen',
	RATSINFORMANT_BASE_URL . '/termine/<termin_id:\d+>/geoExport'                => 'termine/topGeoExport',
	RATSINFORMANT_BASE_URL . '/themen/referat/<referat_url:[a-z0-9_-]+>'         => 'themen/referat',
	RATSINFORMANT_BASE_URL . '/<action:\w+>'                                     => 'index/<action>',
	RATSINFORMANT_BASE_URL . '/<controller:\w+>/<id:\d+>'                        => '<controller>/anzeigen',
	RATSINFORMANT_BASE_URL . '/<controller:\w+>/<action:\w+>/<id:\d+>'           => '<controller>/<action>',
	RATSINFORMANT_BASE_URL . '/<controller:\w+>/<action:\w+>'                    => '<controller>/<action>',
);
