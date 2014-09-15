<?php
/**
 * @var Termin $termin
 */

$this->pageTitle = $termin->getName(true);
$assets_base = $this->getAssetsBase();
$geodata = array();

?>
<h1><?= CHtml::encode($termin->getName(true)) ?></h1>

<div class="row">
	<table class="table table-bordered">
		<tbody>
		<tr>
			<th>Gremium:</th>
			<td>
				<div style="float: right;"><?
					echo CHtml::link("<span class='icon-right-open'></span> Original-Seite im RIS", $termin->getSourceLink());
					?></div>
				<?
				echo CHtml::encode($termin->gremium->name);
				?>
			</td>
		</tr>
		<tr>
			<th>Veranstaltungsreihe</th>
			<td>
				<?
				if ($termin->termin_next_id > 0) {
					$url = Yii::app()->createUrl("termine/anzeigen", array("termin_id" => $termin->termin_next_id));
					echo '<a href="' . CHtml::encode($url) . '" style="float: right;">Nächster Termin <span class="icon-right-open"></span></a>';
				}
				if ($termin->termin_prev_id > 0) {
					$url = Yii::app()->createUrl("termine/anzeigen", array("termin_id" => $termin->termin_prev_id));
					echo '<a href="' . CHtml::encode($url) . '" style="float: left;"><span class="icon-left-open"> Voriger Termin</span></a>';
				}
				?>
			</td>
		</tr>
		<tr>
			<th>Dokumente:</th>
			<td>
				<ul>
					<? foreach ($termin->antraegeDokumente as $dok) {
						echo "<li>" . CHtml::link($dok->name, $dok->getOriginalLink()) . " (vom " . RISTools::datumstring($dok->datum) . ")</li>";
					} ?>
				</ul>
			</td>
		</tr>
		</tbody>
	</table>

	<? if (count($termin->antraegeErgebnisse) > 0) { ?>
		<section id="mapsection">
	<h3>Tagesordnung auf der Karte</h3>
	<div id="mapholder">
		<div id="map"></div>
	</div>
	<a href="<?=Yii::app()->createUrl("termine/topGeoExport", array("termin_id" => $termin->id))?>" style="float: right;">Großversion der Karte exportieren <span class="icon-right-open"></span></a>
		</section>
	<br>

	<h3>Tagesordnung</h3>
	<ol style="list-style-type: none;">
		<?
		$geheimer_teil = false;
		$tops = $termin->ergebnisseSortiert();
		foreach ($tops as $ergebnis) {
			if ($ergebnis->status == "geheim" && !$geheimer_teil) {
				$geheimer_teil = true;
				echo "</ol><h3>Nicht-Öffentlicher Teil</h3><ol style='list-style-type: none;'>";
			}
			$name = $ergebnis->top_nr . ": " . $ergebnis->getName(true);
			echo "<li style='margin-bottom: 7px;'>";
			if ($ergebnis->top_ueberschrift) echo "<strong>";
			echo CHtml::encode($name);
			if ($ergebnis->top_ueberschrift) echo "</strong>";
			$antraege = $ergebnis->zugeordneteAntraegeHeuristisch();
			if (count($ergebnis->dokumente) > 0 || is_object($ergebnis->antrag) || count($antraege) > 0) {
				echo "<ul class='doks'>";
				if (is_object($ergebnis->antrag)) {
					echo "<li>" . CHtml::link("Sitzungsvorlage", $ergebnis->antrag->getLink()) . "</li>\n";
				}
				foreach ($ergebnis->dokumente as $dokument) {
					echo "<li>" . CHtml::link($dokument->name, $dokument->getOriginalLink());
					$x = explode("Beschluss:", $dokument->text_pdf);
					if (count($x) > 1) echo " (" . CHtml::encode(trim($x[1])) . ")";
					echo "</li>\n";
				}
				foreach ($antraege as $ant) if (is_object($ant)) {
					/** @var Antrag $ant */
					echo "<li>Verwandter Antrag: " . CHtml::link($ant->getName(true), $ant->getLink()) . "</li>\n";
				} else {
					echo "<li>Verwandter Antrag: " . CHtml::encode(RISTools::korrigiereTitelZeichen($ant)) . "</li>\n";
				}
				echo "</ul>";
			}
			echo "</li>";

			$geo = $ergebnis->get_geo();
			foreach ($geo as $g) $geodata[] = array(
				FloatVal($g->lat),
				FloatVal($g->lon),
				$ergebnis->top_nr . ": " . $ergebnis->getName(true)
			);
			echo "</li>";
		}
		?>
	</ol>
	<? } else { ?>
		<div class="keine_tops">(Noch) Keine Tagesordnung veröffentlicht</div>
	<? } ?>
</div>


<script>
	$(function() {
		var geodata = <?=json_encode($geodata)?>;
		if (geodata.length > 0) yepnope({
			load: ["/js/Leaflet/leaflet.js", "/js/Leaflet.Fullscreen/Control.FullScreen.js", <?=json_encode($assets_base)?> +"/ba_features.js",
				"/js/Leaflet.draw-0.2.3/dist/leaflet.draw.js",
				"/js/leaflet.spiderfy.js",
				"/js/leaflet.textmarkers.js"
			],
			complete: function () {
				var $map = $("#map").AntraegeKarte({
					outlineBA: <?=($termin->ba_nr > 0 ? $termin->ba_nr : 0)?>
				});
				$map.AntraegeKarte("setAntraegeData", geodata, null);
			}
		});
		else $("#mapsection").hide();
	});
</script>
