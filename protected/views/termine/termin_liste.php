<?php
/**
 * @var array $termine
 * @var bool $gremienname
 * @var bool $twoCols
 */

?>
<ul class="terminliste2 list-group<?php if ($gremienname) echo " mit_gremienname"; if (isset($twoCols) && $twoCols) echo " termine_twocols"; ?>"><?php
	foreach ($termine as $termin) {

		$termine_ids[] = $termin["id"];
		echo '<li class="list-group-item" itemscope itemtype="http://schema.org/Event"><div class="row-action-primary"><i class="glyphicon glyphicon-calendar" title="Termin"></i></div>';
		echo '<div class="row-content"><h4 class="list-group-item-heading">';
		if ($termin["typ"] == Termin::TYP_BUERGERVERSAMMLUNG) {
			echo CHtml::encode($termin["datum_long"]);
			echo '<meta itemprop="name" content="Bürger*innenversammlung">';
		} else {
			echo '<a href="' . CHtml::encode($termin["link"]) . '" itemprop="url">';
			echo CHtml::encode($termin["datum_long"]);
			echo '</a>';
			$gremien = array_keys($termin["gremien"]);
			echo '<meta itemprop="name" content="Bezirksausschuss ' . CHtml::encode(implode(", ", $gremien)) . '">';
		}
		echo '<meta itemprop="startDate" content="' . CHtml::encode($termin["datum_iso"]) . '">';
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

		echo '<address itemprop="location"><span itemprop="address">' . str_replace(", ", "<br>", nl2br(CHtml::encode($termin["ort"]))) . '</span></address>';

		if (count($termin["dokumente"]) > 0) {
			echo '<ul class="dokumentenliste_small">';
			foreach ($termin["dokumente"] as $dokument) {
				/** @var Dokument $dokument */
				echo '<li>' . CHtml::link('<span class="glyphicon glyphicon-file"></span> ' . $dokument->getName(true), $dokument->getLink()) . '</li>';
			}
			echo "</ul>";
		}
		echo '</div></li>';
	}
	?></ul>
