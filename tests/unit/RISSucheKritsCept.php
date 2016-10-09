<?php
$I = new UnitTester($scenario);
$I->wantTo('Test RISSucheKrits');
$I->amOnPage('/themen');

// --- Test data ---
// The geo is the Marienplatz with 1000m radius
// Fist, with all available Krits
$krits_array = [
    ['typ' => 'betreff',            'suchbegriff' => 'value_of_betreff'            ],
    ['typ' => 'volltext',           'suchbegriff' => 'value_of_volltext'           ],
    ['typ' => 'antrag_typ',         'suchbegriff' => 'stadtrat_antrag'             ],
    ['typ' => 'antrag_wahlperiode', 'suchbegriff' => 'value_of_antrag_wahlperiode' ],
    ['typ' => 'ba',                 'ba_nr'       => 0                             ],
    ['typ' => 'referat',            'referat_id'  => 1                             ],
    ['typ' => 'antrag_nr',          'suchbegriff' => 'valueofantragnr'             ],
    ['typ' => 'geo', 'lng' => 48.137079, 'lat' => 11.576006, 'radius' => 1000      ],
];

$krits_url_parts = [
    'krit_typ[]=betreff&krit_val[]=value_of_betreff',
    'krit_typ[]=volltext&krit_val[]=value_of_volltext',
    'krit_typ[]=antrag_typ&krit_val[]=stadtrat_antrag',
    'krit_typ[]=antrag_wahlperiode&krit_val[]=value_of_antrag_wahlperiode',
    'krit_typ[]=ba&krit_val[]=0',
    'krit_typ[]=referat&krit_val[]=1',
    'krit_typ[]=antrag_nr&krit_val[]=valueofantragnr',
    'krit_typ[]=geo&krit_val[]=48.137079-11.576006-1000',
];

$krits_url_array = [
    'krit_typ' =>
        [
            0 => 'betreff',
            1 => 'volltext',
            2 => 'antrag_typ',
            3 => 'antrag_wahlperiode',
            4 => 'ba',
            5 => 'referat',
            6 => 'antrag_nr',
            7 => 'geo',
        ],
    'krit_val' =>
        [
            0 => 'value_of_betreff',
            1 => 'value_of_volltext',
            2 => 'stadtrat_antrag',
            3 => 'value_of_antrag_wahlperiode',
            4 => 0,
            5 => 1,
            6 => 'valueofantragnr',
            7 => '48.137079-11.576006-1000',
        ],
];

$all_krits_description = 'Dokumente mit "value_of_betreff" im Betreff, ' .
    'mit dem Suchausdruck "value_of_volltext", ' .
    'vom Typ "Stadtratsantrag", ' .
    'aus der Wahlperiode value_of_antrag_wahlperiode, ' .
    'aus dem Bezirksausschuss 0: Stadtrat, ' .
    'im Zuständigkeitsbereich des Referat für städtische Aufgaben, '.
    'zum Antrag Nr. valueofantragnr ' .
    'und mit einem Ortsbezug (ungefähr: 1000m um "Marienplatz")';

$single_krits_description = [
    'Dokumente mit "value_of_betreff" im Betreff',
    'Volltextsuche nach "value_of_volltext"',
    'Dokumente des Typs "Stadtratsantrag"',
    'Dokumente der Wahlperiode value_of_antrag_wahlperiode',
    'Bezirksausschuss 0: Stadtrat',
    'Referat für städtische Aufgaben',
    'Antrag Nr. valueofantragnr',
    'Dokumente mit Ortsbezug (ungefähr: 1000m um "Marienplatz")',
];

$solr_query_strings = [
    'antrag_betreff:value_of_betreff',
    'value_of_volltext',
    'antrag_typ:stadtrat_antrag',
    'antrag_wahlperiode:value_of_antrag_wahlperiode',
    'dokument_bas:0',
    'referat_id:1',
    '*valueofantragnr*',
    '{!geofilt pt=11.576006,48.137079 sfield=geo d=1}',
];

$krits_array_without_wahlperiode = $krits_array;
// Remove the antrag_wahlperiode
array_splice($krits_array_without_wahlperiode, 3, 1);

// Orte
$inside      = new OrtGeo();
$inside->ort = "inside";
$inside->lat = 11.576006;
$inside->lon = 48.137079;

$outside      = new OrtGeo();
$outside->ort = "outside";
$outside->lat = -11.576006;
$outside->lon = -48.137079;

// --- Tests ---

// Test every single krits on it's own
foreach ($krits_array as $i => $val) {
    $krits = new RISSucheKrits();

    if ($val['typ'] != 'geo') {
        $krits->addKrit($val['typ'], array_values($val)[1]);
        $I->assertFalse($krits->isGeoKrit());
        $I->assertNull($krits->getGeoKrit());

        // No geo-Krit means "always inside"
        $I->assertTrue($krits->filterGeo($inside));
        $I->assertTrue($krits->filterGeo($outside));
    } else {
        $krits->addKrit($val['typ'], array_values($val)[1] . '-' . array_values($val)[2] . '-' .  array_values($val)[3]);
        $I->assertTrue($krits->isGeoKrit());

        $I->assertTrue($krits->filterGeo($inside));
        $I->assertFalse($krits->filterGeo($outside));
        $I->assertEquals($krits->getGeoKrit(), $krits->krits[0]);
    }

    $I->assertEquals($krits->getKritsCount(), 1);
    $I->assertEquals($krits, $krits->cloneKrits());
    $I->assertEquals($krits->getJson(), json_encode([$val]));

    $url_array = [
        'krit_typ' => [$krits_url_array['krit_typ'][$i]],
        'krit_val' => [$krits_url_array['krit_val'][$i]],
    ];
    $I->assertEquals('/suche/?' . $krits_url_parts[$i], $krits->getUrl());
    $I->assertEquals($url_array, $krits->getUrlArray());
    $I->assertEquals($krits, RISSucheKrits::createFromUrl($url_array));

    if ($val['typ'] != 'antrag_wahlperiode') {
        $I->assertEquals($krits->getBenachrichtigungKrits(), $krits);
        $I->assertEquals($krits->getFeedUrl(), $krits->getUrl('index/feed'));
    } else {
        $I->assertEquals($krits->getBenachrichtigungKrits(), new RISSucheKrits());
        $I->assertEquals($krits->getFeedUrl(), (new RISSucheKrits())->getUrl('index/feed'));
    }

    $I->assertEquals($krits->getBeschreibungDerSuche(), $single_krits_description[$i]);

    $solr = RISSolrHelper::getSolrClient();
    $select = $solr->createSelect();
    $I->assertEquals($krits->getSolrQueryStr($select), $solr_query_strings[$i]);
}

// Test with all krits
$krits = new RISSucheKrits($krits_array);
$I->assertEquals($krits, $krits->cloneKrits());
$I->assertEquals($krits->getKritsCount(), count($krits_array));
$I->assertEquals($krits->getJson(), json_encode($krits_array));

$I->assertEquals('/suche/?' . implode('&', $krits_url_parts), $krits->getUrl());
$I->assertEquals($krits, RISSucheKrits::createFromUrl($krits_url_array));
$I->assertEquals($krits_url_array, $krits->getUrlArray());

$I->assertEquals($krits->isGeoKrit(), true);
$I->assertEquals($krits->getGeoKrit(), $krits_array[7]);
$I->assertTrue($krits->filterGeo($inside));
$I->assertFalse($krits->filterGeo($outside));

$I->assertEquals($krits->getBenachrichtigungKrits()->krits, $krits_array_without_wahlperiode);
$I->assertEquals($krits->getBeschreibungDerSuche(), $all_krits_description);

$I->assertEquals($krits->getFeedUrl(), $krits->getBenachrichtigungKrits()->getUrl('index/feed'));

// Special cases
$I->assertEquals(new RISSucheKrits(), RISSucheKrits::createFromUrl([]));
