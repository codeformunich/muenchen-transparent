<?php

/**
 * @var IndexController $this
 * @var Antrag[] $antraege
 * @var string $titel
 */

?>
	<h3><?=CHtml::encode($titel)?></h3>
	<ul class="antragsliste">
		<?
		foreach ($antraege as $ant) if (!method_exists($ant, "getName")) {
			echo "<li>" . get_class($ant) . "</li>";
		} else {
			echo "<li><div class='antraglink'>" . CHtml::link($ant->getName(), $ant->getLink()) . "</div>";

			$max_dok_date = 0;
			$doklist  = "";
			foreach ($ant->dokumente as $dokument) {
				$doklist .= "<li>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</li>";
				$dat = RISTools::date_iso2timestamp($dokument->datum);
				if ($dat > $max_dok_date) $max_dok_date = $dat;
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
				$str = "<span class='partei' title='" . CHtml::encode(implode(", ", $personen)) . "'>";
				$str .= CHtml::encode($partei);
				$str .= "</span>";
				$p_strs[] = $str;
			}
			if (count($p_strs) > 0) echo implode(", ", $p_strs) . ", ";
			//echo date("d.m.", $max_date);

			if ($ant->ba_nr > 0) echo " <span title='" . CHtml::encode("Bezirksausschuss " . $ant->ba_nr . " (" . $ant->ba->name . ")") . "' class='ba'>BA " . $ant->ba_nr . "</span>, ";

			echo date("d.m.", RISTools::date_iso2timestamp($ant->datum_letzte_aenderung));
			echo "</div>";

			echo "<ul class='dokumente'>";
			echo $doklist;
			echo "</ul></li>\n";
		}
		?>
	</ul>
