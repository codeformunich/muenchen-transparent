<?php
// Preface: The paper objects with the ids 1 to 5 have 2016-05-01 00:00:00 to 2016-05-05 00:00:00 as values for modified and changed
$I = new OparlTester($scenario);
$I->wantTo('Test that the list filters work in every possible combination');

/**
 * Helper functions that there were $a items returns if the delimiter isn't 'since' or $b items otherwise. It also checks
 * that the response is equal with and without url encoding
 * @param $url
 * @param $timestamp
 * @param $delimiter
 * @param $a
 * @param $b
 */
$checkItemCount = function ($url, $timestamp, $delimiter, $a, $b) use ($I) {
    $I->getOParl($url . '=' . urlencode($timestamp), true);
    $old = $I->getPrettyPrintedResponse();
    $I->getOParl($url . '=' . $timestamp);
    $I->assertEquals($I->getPrettyPrintedResponse(), $old);
    $I->assertEquals(count($I->getResponseAsTree()->items), $delimiter != 'since' ? $a : $b);
};

// Set some constants
$base = '/body/0/list/paper?';
$I->getOParl($base);
$min_item_count = 0;
$max_item_count = $I->getResponseAsTree()->itemsPerPage; // Constant for all request as it's defined only once

foreach (['modified_', 'created_'] as $field) {
    // Check possible combination with a single filter
    foreach (['until', 'since'] as $delimiter) {
        $url = $base . $field . $delimiter;

        // The normal cases
        $checkItemCount($url, '2016-05-01T00:00:00+02:00', $delimiter, 1, $max_item_count);
        $checkItemCount($url, '2016-05-02T00:00:00+02:00', $delimiter, 2, $max_item_count);
        $checkItemCount($url, '2016-05-03T00:00:00+02:00', $delimiter, 3, $max_item_count);

        // The extreme cases
        $checkItemCount($url, '1016-05-01T00:00:00+02:00', $delimiter, $min_item_count, $max_item_count);
        $checkItemCount($url, '3016-05-01T00:00:00+02:00', $delimiter, $max_item_count, $min_item_count);
    }

    // Combined filters
    $I->getOParl($base . $field . 'since=2016-05-01T00:00:00+02:00' . '&' . $field . 'until=2016-05-02T00:00:00+02:00');
    $I->assertEquals(count($I->getResponseAsTree()->items), 2);
}

// combining all 4 filter
$four_filters = $base .
    'modified_since=2016-05-03T00:00:00+02:00' . '&' .
    'modified_until=2016-05-05T00:00:00+02:00' . '&' .
    'created_since=2016-05-02T00:00:00+02:00' . '&' .
    'created_until=2016-05-04T00:00:00+02:00';
$I->getOParl($four_filters);
$I->assertEquals(count($I->getResponseAsTree()->items), 2);
$I->assertEquals($I->getResponseAsTree()->items[0]->created, '2016-05-03T00:00:00+02:00');
$I->assertEquals($I->getResponseAsTree()->items[1]->created, '2016-05-04T00:00:00+02:00');

// combining all 4 filter with pagination via id
$I->getOParl($four_filters . '&' . 'id=3');
$I->assertEquals(count($I->getResponseAsTree()->items), 1);
$I->assertEquals($I->getResponseAsTree()->items[0]->created, '2016-05-04T00:00:00+02:00');
