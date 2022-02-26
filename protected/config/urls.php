<?php

$GLOBALS["RIS_URL_RULES"] = [
    SITE_BASE_URL . '/'                                                   => 'index/startseite',
    SITE_BASE_URL . '/ajax-<datum_max:[0-9\-]+>'                          => 'index/antraegeAjax',
    SITE_BASE_URL . '/bezirksausschuss'                                   => 'index/baListe',
    SITE_BASE_URL . '/bezirksausschuss/<ba_nr:\d+>_<ba_name:[^\/]*>'      => 'index/ba',
    SITE_BASE_URL . '/bezirksausschuss/<ba_nr:\d+>/termine'               => 'termine/baTermineAlle',
    SITE_BASE_URL . '/bezirksausschuss/<ba_nr:\d+>/dokumente'             => 'index/baDokumente',
    SITE_BASE_URL . '/bezirksausschuss/<ba_nr:\d+>'                       => 'index/ba',
    SITE_BASE_URL . '/dokumente/<id:[0-9-]+>'                             => 'index/dokumente',
    SITE_BASE_URL . '/dokumente/<id:[0-9-]+>.pdf'                         => 'index/documentProxy', // Alte Variante; Sollte irgendwann(TM) gelöscht werden; TODO: redirect
    SITE_BASE_URL . '/dokumente/<id:[0-9-]+>/datei'                       => 'index/documentProxy',
    SITE_BASE_URL . '/stadtraetIn/<id:\d+>_<name:[^\/]*>'                 => 'personen/personAlt',
    SITE_BASE_URL . '/personen/<id:\d+>_<name:[^\/]*>'                    => 'personen/person',
    SITE_BASE_URL . '/personen/<id:\d+>'                                  => 'personen/person',
    SITE_BASE_URL . '/personen/'                                          => 'personen/index',
    SITE_BASE_URL . '/tiles/<width:\d+>/<zoom:\d+>/<x:\d+>/<y:\d+>.png'   => 'index/tileCache',
    SITE_BASE_URL . '/admin/'                                             => 'admin/index',
    SITE_BASE_URL . '/benachrichtigungen'                                 => 'benachrichtigungen/index',
    SITE_BASE_URL . '/benachrichtigungen/alleFeed/<code:[0-9\-a-z]+>'     => 'benachrichtigungen/alleFeed',
    SITE_BASE_URL . '/termine/<termin_id:\d+>'                            => 'termine/anzeigen',
    SITE_BASE_URL . '/termine/<termin_id:\d+>/geoExport'                  => 'termine/topGeoExport',
    SITE_BASE_URL . '/termine/<termin_id:\d+>/icsSingle'                  => 'termine/icsExportSingle',
    SITE_BASE_URL . '/termine/<termin_id:\d+>/icsAll'                     => 'termine/icsExportAll',
    SITE_BASE_URL . '/termine/'                                           => 'termine/index',
    SITE_BASE_URL . '/themen/referat/<referat_url:[a-z0-9_-]+>'           => 'themen/referat',
    SITE_BASE_URL . '/themen/schlagwort/<tag_id:\d+>_<tag_name:[^\/]+>'   => 'themen/tag',
    SITE_BASE_URL . '/themen/'                                            => 'themen/index',
    SITE_BASE_URL . '/export/fraktionantraege/<fraktion_id:\w+>'          => 'export/fraktionantraege',

    // Oparl
    OPARL_10_ROOT                                                         => 'oparl10/system',
    OPARL_10_ROOT . '/fileaccess/<mode:\w+>/<id:-?\d+>'                   => 'oparl10/fileaccess',
    OPARL_10_ROOT . '/<typ:\w+>/<id:\d+>'                                 => 'oparl10/object',
    OPARL_10_ROOT . '/<typ:\w+>/<subtype:\w+>/<id:-?\d+>'                 => 'oparl10/object',
    OPARL_10_ROOT . '/list/body'                                          => 'oparl10/externalListBody',
    OPARL_10_ROOT . '/body/<body:\d+>/list/<typ:\w+>'                     => 'oparl10/externalList',

    SITE_BASE_URL . '/<action:\w+>'                                       => 'index/<action>',
    SITE_BASE_URL . '/<controller:\w+>/<id:\d+>'                          => '<controller>/anzeigen',
    SITE_BASE_URL . '/<controller:\w+>/<action:\w+>/<id:\d+>'             => '<controller>/<action>',
    SITE_BASE_URL . '/<controller:\w+>/<action:\w+>'                      => '<controller>/<action>',
];
