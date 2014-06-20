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
	<h3>Tagesordnung auf der Karte</h3>
	<div id="mapholder">
		<div id="map"></div>
	</div>
	<a href="<?=Yii::app()->createUrl("termine/topGeoExport", array("termin_id" => $termin->id))?>" style="float: right;">Großversion der Karte exportieren <span class="icon-right-open"></span></a>
	<br>

	<h3>Tagesordnung</h3>
	<ol style="list-style-type: none;">
		<?
		foreach ($termin->antraegeErgebnisse as $ergebnis) {
			echo "<li style='margin-bottom: 7px;'>";
			if ($ergebnis->top_ueberschrift) echo "<strong>";
			$name = $ergebnis->top_nr . ": " . $ergebnis->top_betreff;
			if (!is_object($ergebnis->antrag)) echo "<li>" . CHtml::encode($name) . "</li>";
			else echo "<li>" . CHtml::link($name, $ergebnis->antrag->getLink()) . "</li>";
			if ($ergebnis->top_ueberschrift) echo "</strong>";
			$geo = $ergebnis->get_geo();
			foreach ($geo as $g) $geodata[] = array(
				FloatVal($g->lat),
				FloatVal($g->lon),
				$ergebnis->top_nr . ": " . $ergebnis->top_betreff
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
	yepnope({
		load: ["/js/Leaflet/leaflet.js", "/js/leaflet.fullscreen/Control.FullScreen.js", <?=json_encode($assets_base)?> +"/ba_features.js",
			"/js/Leaflet.draw/dist/leaflet.draw.js",
			"/js/OverlappingMarkerSpiderfier-Leaflet/oms.min.js",
			"/js/leaflet.textmarkers.js"
		],
		complete: function () {
			var $map = $("#map").AntraegeKarte({
				outlineBA: <?=($termin->ba_nr > 0 ? $termin->ba_nr : 0)?>
			});
			$map.AntraegeKarte("setAntraegeData", <?=json_encode($geodata)?>, null);
		}
	});
</script>