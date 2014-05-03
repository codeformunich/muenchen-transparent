<?php

/**
 * @var IndexController $this
 * @var Antrag[] $antraege
 * @var string|null $weitere_url
 * @var string $datum
 * @var float $geo_lng
 * @var float $geo_lat
 * @var float $radius
 * @var OrtGeo $naechster_ort
 */

if (isset($datum)) {
	$erkl_str = RISTools::datumstring($datum);
	if ($erkl_str > 0) $erkl_str = "vom " . $erkl_str;
	else $erkl_str = "von " . $erkl_str;
} else {
	$erkl_str = ": etwa ${radius}m um \"" . CHtml::encode($naechster_ort->ort) . "\"";
}
?>
<h3>Stadtratsdokumente <?= $erkl_str ?></h3>
<ul class="antragsliste">
	<?
	foreach ($antraege as $ant) if (!method_exists($ant, "getName")) {
		echo "<li>" . get_class($ant) . "</li>";
	} else {
		echo "<li><div class='antraglink'><a href='" . CHtml::encode($ant->getLink()) . "' title='" . CHtml::encode($ant->getName()) . "'>";
		echo CHtml::encode($ant->getName()) . "</a></div>";

		$max_date = 0;
		$doklist  = "";
		foreach ($ant->dokumente as $dokument) {
			//$doklist .= "<li>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</li>";
			$dokurl = $dokument->getOriginalLink();
			$doklist .= "<li><a href='" . CHtml::encode($dokurl) . "'";
			if (substr($dokurl, strlen($dokurl) - 3) == "pdf") $doklist .= ' class="pdf"';
			$doklist .= ">" . CHtml::encode($dokument->name) . "</a></li>";
			$dat = RISTools::date_iso2timestamp($dokument->datum);
			if ($dat > $max_date) $max_date = $dat;
		}

		echo "<div class='add_meta'>";
		$parteien = array();
		foreach ($ant->antraegePersonen as $person) {
			$name   = $person->person->name;
			$partei = $person->person->ratePartei($ant->gestellt_am);
			if (!$partei) {
				$parteien[$name] = array($name);
			} else {
				if (!isset($parteien[$partei])) $parteien[$partei] = array();
				$parteien[$partei][] = $person->person->name;
			}
		}

		$p_strs = array();
		foreach ($parteien as $partei => $personen) {
			$personen_net = array();
			foreach ($personen as $p) if ($p != $partei) $personen_net[] = $p;
			$str = "<span class='partei' title='" . CHtml::encode(implode(", ", $personen_net)) . "'>";
			$str .= CHtml::encode($partei);
			$str .= "</span>";
			$p_strs[] = $str;
		}
		if (count($p_strs) > 0) echo implode(", ", $p_strs) . ", ";

		if ($ant->ba_nr > 0) echo "<span title='" . CHtml::encode("Bezirksausschuss " . $ant->ba_nr . " (" . $ant->ba->name . ")") . "' class='ba'>BA " . $ant->ba_nr . "</span>, ";

		echo date("d.m.", $max_date);
		echo "</div>";

		echo "<ul class='dokumente'>";
		echo $doklist;
		echo "</ul></li>\n";
	}
	?>
</ul>
<? if ($weitere_url !== null) { ?>
<div class="aeltere_caller">
	<a href="#" onClick="return index_aeltere_dokumente_load('<?= CHtml::encode($weitere_url) ?>');"><span class="glyphicon glyphicon-chevron-right"></span> Ältere Dokmente</a>
</div>
<? } ?>