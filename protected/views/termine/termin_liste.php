<?php
/**
 * @var Termin[] $termine
 */

?>
<ul class="terminliste"><?
	foreach ($termine as $termin) {
		$termine_ids[] = $termin["id"];
		echo "<li><div class='termin'>" . CHtml::encode($termin["datum"] . ", " . $termin["ort"]) . "</div><div class='termindetails'>";
		$gremien = array();
		foreach ($termin["gremien"] as $name => $links) {
			foreach ($links as $link) $gremien[] = CHtml::link($name, $link);
		}
		echo implode(", ", $gremien);
		echo "</div>";

		if (count($termin["dokumente"]) > 0) {
			echo "<ul class='dokumente'>";
			foreach ($termin["dokumente"] as $dokument) {
				/** @var AntragDokument $dokument */
				echo "<li>" . CHtml::link($dokument->name, $dokument->getOriginalLink()) . "</li>";
			}
			echo "</ul>";
		}
		echo "</li>";
	}
	?></ul>