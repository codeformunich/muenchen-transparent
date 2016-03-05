<?php

/**
 * @var IndexController
 * @var StadtraetIn[]   $stadtraetInnen
 */
$this->layout = false;
header('Content-type: application/json; charset=UTF-8');

$output_data = [];
foreach ($stadtraetInnen as $str) {
    $fraktion = "";
    $gremien  = [];
    foreach ($str->stadtraetInnenFraktionen as $fr) {
        $fraktion                                     = $fr->fraktion->name;
        $gremium                                      = ($fr->fraktion->ba_nr > 0 ? "BA ".$fr->fraktion->ba_nr : "Stadtrat");
        if (!in_array($gremium, $gremien)) $gremien[] = $gremium;
    } // @TODO

    $output_data[] = [
        "value"      => $str->getName(),
        "fraktionen" => $fraktion,
        "gremien"    => implode(", ", $gremien),
        "url"        => $str->getLink(),
    ];
}

echo json_encode($output_data);
