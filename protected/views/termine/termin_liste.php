<?php
/**
 * @var array $termine
 * @var bool $gremienname
 */

?>
<ul class="terminliste2 list-group<? if ($gremienname) echo " mit_gremienname"; ?>"><?
	foreach ($termine as $termin) {

		$termine_ids[] = $termin["id"];
		echo '<li class="list-group-item"><div class="row-action-primary"><i class="glyphicon glyphicon-calendar" title="Termin"></i></div>';
		echo '<div class="row-content"><h4 class="list-group-item-heading">';
		echo CHtml::link($termin["datum_long"], $termin["link"]);
		echo '</h4>';

		if ($gremienname) {
			echo '<div class="termindetails">';
			$gremien = array();
			foreach ($termin["gremien"] as $name => $links) {
				foreach ($links as $link) $gremien[] = CHtml::link($name, $link);
			}
			echo implode(", ", $gremien);
			echo '</div>';
		}

		echo '<address>' . str_replace(", ", "<br>", CHtml::encode($termin["ort"])) . '</address>';

		if (count($termin["dokumente"]) > 0) {
			echo '<ul class="dokumentenliste_small">';
			foreach ($termin["dokumente"] as $dokument) {
				/** @var Dokument $dokument */
				echo '<li>' . CHtml::link('<span class="glyphicon glyphicon-file"></span> ' . $dokument->getName(true), $dokument->getLinkZumDokument()) . '</li>';
			}
			echo "</ul>";
		}
		echo '</div></li>';
	}
	?></ul>