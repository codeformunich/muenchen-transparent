<?php

/**
 * @var IndexController $this
 * @var array $geodata
 * @var Antrag[] $antraege
 * @var string $datum
 * @var string $datum_pre
 */


?>
<h3>Stadtratsdokumente</h3>
<ul class="antragsliste">
	<?
	foreach ($antraege as $ant) {
		echo "<li><div class='antraglink'>" . CHtml::link($ant->getName(), $ant->getLink()) . "</div>";

		$max_date = 0;
		$doklist = "";
		foreach ($ant->dokumente as $dokument) {
			$doklist .= "<li>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</li>";
			$dat = RISTools::date_iso2timestamp($dokument->datum);
			if ($dat > $max_date) $max_date = $dat;
		}

		echo "<div class='add_meta'>";
		$parteien = array();
		foreach ($ant->antraegePersonen as $person) {
			$name = $person->person->name;
			$partei = $person->person->ratePartei();
			if (!$partei) {
				$parteien[$name] = array($name);
			} else {
				if (!isset($parteien[$partei])) $parteien[$partei] = array();
				$parteien[$partei][] = $person->person->name;
			}
		}

		$p_strs = array();
		foreach ($parteien as $partei => $personen) {
			$str = "<span class='partei' title='" . CHtml::encode(implode(", ", $personen)) . "'>";
			$str .= CHtml::encode($partei);
			$str .= "</span>";
			$p_strs[] = $str;
		}
		if (count($p_strs) > 0) echo implode(", ", $p_strs) . ", ";
		echo date("d.m.", $max_date);
		echo "</div>";

		echo "<ul class='dokumente'>";
		echo $doklist;
		echo "</ul></li>\n";
	}
	?>
</ul>
<a href="#" onClick="return index_aeltere_dokumente_load('<?=CHtml::encode($this->createUrl("index/antraegeAjax", array("datum_max" => $datum_pre)))?>');"><span class="glyphicon glyphicon-chevron-right"></span> Ältere Dokmente</a>