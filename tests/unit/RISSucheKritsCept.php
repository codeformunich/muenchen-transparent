<?php
$I = new UnitTester($scenario);
$I->wantTo('Test RISSucheKrits');
$I->amOnPage('/themen');

// Get the test data
require "RISSucheKritsData.php";

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

    // URL
    $url_array = [
        'krit_typ' => [$krits_url_array['krit_typ'][$i]],
        'krit_val' => [$krits_url_array['krit_val'][$i]],
    ];
    $I->assertEquals('/suche/?' . $krits_url_parts[$i], $krits->getUrl());
    $I->assertEquals($url_array, $krits->getUrlArray());
    $I->assertEquals($krits, RISSucheKrits::createFromUrl($url_array));

    // Benachritigungen
    if ($val['typ'] != 'antrag_wahlperiode') {
        $I->assertEquals($krits->getBenachrichtigungKrits(), $krits);
        $I->assertEquals($krits->getFeedUrl(), $krits->getUrl('index/feed'));
    } else {
        $I->assertEquals($krits->getBenachrichtigungKrits(), new RISSucheKrits());
        $I->assertEquals($krits->getFeedUrl(), (new RISSucheKrits())->getUrl('index/feed'));
    }

    // solr
    $solr = RISSolrHelper::getSolrClient();
    $select = $solr->createSelect();
    $I->assertEquals($krits->getSolrQueryStr($select), $solr_query_strings[$i]);

    // other
    $I->assertEquals($krits->getBeschreibungDerSuche(), $single_krits_description[$i]);
    $I->assertTrue($krits->hasKrit($val['typ']), $val['typ']);
    $I->assertFalse($krits->hasKrit("invalid"));
}

// Test with all krits
$krits = new RISSucheKrits($krits_array);
$I->assertEquals($krits, $krits->cloneKrits());
$I->assertEquals($krits->getKritsCount(), count($krits_array));
$I->assertEquals($krits->getJson(), json_encode($krits_array));

// URL
$I->assertEquals('/suche/?' . implode('&', $krits_url_parts), $krits->getUrl());
$I->assertEquals($krits, RISSucheKrits::createFromUrl($krits_url_array));
$I->assertEquals($krits_url_array, $krits->getUrlArray());
$I->assertEquals($krits->getFeedUrl(), $krits->getBenachrichtigungKrits()->getUrl('index/feed'));

// geo
$I->assertEquals($krits->isGeoKrit(), true);
$I->assertEquals($krits->getGeoKrit(), $krits_array[7]);
$I->assertTrue($krits->filterGeo($inside));
$I->assertFalse($krits->filterGeo($outside));

// solr
$solr   = RISSolrHelper::getSolrClient();
$select = $solr->createSelect();
$krits->addKritsToSolr($select);
$I->assertEquals($solr_filter_queries, $select->getFilterQueries());

// other
$I->assertEquals($krits->getBeschreibungDerSuche(), $all_krits_description);
$I->assertEquals($krits->getBenachrichtigungKrits()->krits, $krits_array_without_wahlperiode);
foreach ($krits_array as $krit) $I->assertTrue($krits->hasKrit($krit["typ"]));
$I->assertFalse($krits->hasKrit("invalid"));

// Special cases
$I->assertEquals(new RISSucheKrits(), RISSucheKrits::createFromUrl([]));
$I->expectException(Exception::class, function() { (new RISSucheKrits())->addKrit("invalid", null); } );
