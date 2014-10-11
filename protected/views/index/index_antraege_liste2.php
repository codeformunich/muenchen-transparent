<?php

/**
 * @var IndexController $this
 * @var Antrag[] $antraege
 * @var string|null $neuere_url_ajax
 * @var string|null $neuere_url_std
 * @var string|null $aeltere_url_ajax
 * @var string|null $aeltere_url_std
 * @var bool $weiter_links_oben
 * @var string $datum
 * @var string $title
 * @var float $geo_lng
 * @var float $geo_lat
 * @var float $radius
 * @var OrtGeo $naechster_ort
 */

if (isset($title) && $title !== null) {
	$erkl_str = CHtml::encode($title);
} elseif (isset($datum)) {
	if ($datum == date("Y-m-d", time() - 3600 * 24) . " 00:00:00") $erkl_str = "des letzten Tages";
	else {
		$erkl_str = RISTools::datumstring($datum);
		if ($erkl_str > 0) $erkl_str = "vom " . $erkl_str;
		else $erkl_str = "von " . $erkl_str;
	}
	$erkl_str = "Stadtratsdokumente " . $erkl_str;
} elseif (isset($datum_von) && isset($datum_bis)) {
	$erkl_str = "Dokumente vom " . RISTools::datumstring($datum_von) . " bis " . RISTools::datumstring($datum_bis);
} else {
	$erkl_str = "Stadtratsdokumente: etwa ${radius}m um \"" . CHtml::encode($naechster_ort->ort) . "\"";
}


if (count($antraege) > 0) {
	echo '<h3>' . $erkl_str . '</h3><br>';

	if ($weiter_links_oben) {
		if (isset($neuere_url_ajax) && $neuere_url_ajax !== null) {
			?>
			<div class="neuere_caller">
				<a href="<?= CHtml::encode($neuere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($neuere_url_ajax) ?>');" rel="next"><span
						class="glyphicon glyphicon-chevron-left"></span> Neuere Dokmente</a>
			</div>
		<?
		}
		if (isset($aeltere_url_ajax) && $aeltere_url_ajax !== null) {
			?>
			<div class="aeltere_caller">
				<a href="<?= CHtml::encode($aeltere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($aeltere_url_ajax) ?>');" rel="next">Ältere
					Dokmente <span class="glyphicon glyphicon-chevron-right"></span></a>
			</div>
		<?
		}
	}
	echo '<div class="antragsliste2">';
	foreach ($antraege as $ant) if (!method_exists($ant, "getName")) {
		echo '<div class="panel panel-danger">
    <div class="panel-heading">Fehler</div><div class="panel-body">' . get_class($ant) . "</div></div>";
	} else {
		$titel = $ant->getName(true);
		echo '<div class="panel panel-primary">
    <div class="panel-heading"><a href="' . CHtml::encode($ant->getLink()) . '"';
		if (strlen($titel) > 110) echo ' title="' . CHtml::encode($titel) . '"';
		echo '><span>';
		echo CHtml::encode($titel) . '</a></span></div>';
		echo '<div class="panel-body">';

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
		echo "</ul></div></div>\n";
	}
	echo '</div>';
}


if (isset($neuere_url_ajax) && $neuere_url_ajax !== null) {
	?>
	<div class="neuere_caller">
		<a href="<?= CHtml::encode($neuere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($neuere_url_ajax) ?>');" rel="next"><span
				class="glyphicon glyphicon-chevron-left"></span> Neuere Dokmente</a>
	</div>
<?
}
if (isset($aeltere_url_ajax) && $aeltere_url_ajax !== null) {
	?>
	<div class="aeltere_caller">
		<a href="<?= CHtml::encode($aeltere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($aeltere_url_ajax) ?>');" rel="next">Ältere Dokmente
			<span class="glyphicon glyphicon-chevron-right"></span></a>
	</div>
<?
}
?>