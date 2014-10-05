<?php

/**
 * @var IndexController $this
 * @var StadtraetIn[] $stadtraetInnen
 */

$this->layout=false;
header('Content-type: application/json; charset=UTF-8');


$output_data = array();
foreach ($stadtraetInnen as $str) {
	$fraktion = "";
	$gremien = array();
	foreach ($str->stadtraetInnenFraktionen as $fr) {
		$fraktion = $fr->fraktion->name;
		$gremium = ($fr->fraktion->ba_nr > 0 ? "BA " . $fr->fraktion->ba_nr : "Stadtrat");
		if (!in_array($gremium, $gremien)) $gremien[] = $gremium;
	} // @TODO

	$output_data[] = array(
		"value" => $str->name,
		"fraktionen" => $fraktion,
		"gremien" => implode(", ", $gremien),
		"url" => $str->getLink()
	);
}

echo json_encode($output_data);