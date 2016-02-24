<?php


/**
 * @var TermineController $this
 * @var array $termine
 */

Header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"BA-Termine.csv\"");

echo "BA;Datum;Name;Ort\n";
foreach ($termine as $termin) {
    echo $termin["ba_nr"] . ";";
    echo "\"" . $termin["termin"] . "\";";
    echo "\"" . str_replace("\"", "\\\"", $termin["name"]) . "\";";
    echo "\"" . str_replace("\"", "\\\"", $termin["sitzungsort"]) . "\"";
    echo "\n";
}
