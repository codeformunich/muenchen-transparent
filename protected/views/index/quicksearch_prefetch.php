<?php

/**
 * @var IndexController $this
 * @var StadtraetIn[] $stadtraetInnen
 */

$this->layout=false;
header('Content-type: application/json; charset=UTF-8');


$output_data = [];
foreach ($stadtraetInnen as $str) {
	$fraktion = "";
	$gremien = [];
    $memberships = array_merge(
        $str->getMembershipsByType(Gremium::TYPE_STR_FRAKTION),
        $str->getMembershipsByType(Gremium::TYPE_BA_FRAKTION),
    );
	foreach ($memberships as $fr) {
		$fraktion = $fr->gremium->name;
		$gremium = ($fr->gremium->ba_nr > 0 ? "BA " . $fr->gremium->ba_nr : "Stadtrat");
		if (!in_array($gremium, $gremien)) $gremien[] = $gremium;
	} // @TODO

	$output_data[] = [
		"value" => $str->getName(),
		"fraktionen" => $fraktion,
		"gremien" => implode(", ", $gremien),
		"url" => $str->getLink()
	];
}

echo json_encode($output_data);