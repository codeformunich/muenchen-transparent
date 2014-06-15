<?php
/**
 * @var Termin $termin
 */

$this->pageTitle = $termin->getName(true);
$assets_base = $this->getAssetsBase();

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
					$url = Yii::app()->createUrl("index/terminAnzeige", array("termin_id" => $termin->termin_next_id));
					echo '<a href="' . CHtml::encode($url) . '" style="float: right;">Nächster Termin <span class="icon-right-open"></span></a>';
				}
				if ($termin->termin_prev_id > 0) {
					$url = Yii::app()->createUrl("index/terminAnzeige", array("termin_id" => $termin->termin_prev_id));
					echo '<a href="' . CHtml::encode($url) . '" style="float: left;"><span class="icon-left-open"> Voriger Termin</span></a>';
				}
				?>
			</td>
		</tr>
		</tbody>
	</table>

	<h3>Tagesordnung auf der Karte</h3>
	<div id="mapholder">
		<div id="map"></div>
	</div>
	<a href="<?=Yii::app()->createUrl("index/terminAnzeigeGeoExport", array("termin_id" => $termin->id))?>" style="float: right;">Großversion der Karte exportieren <span class="icon-right-open"></span></a>
	<br>

	<h3>Tagesordnung</h3>
	<ol style="list-style-type: none;">
		<?
		$geodata = array();
		foreach ($termin->antraegeErgebnisse as $ergebnis) {
			echo "<li style='margin-bottom: 7px;'>";
			echo CHtml::encode($ergebnis->top_nr . ": " . $ergebnis->top_betreff);
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
	<div id="debugimg"></div>
</div>


<script>
	function manipulateCanvasFunction(savedMap) {
		var dataURL = savedMap.toDataURL("image/png");
		console.log(dataURL);
		var $img = $("<img>");
		$img.attr("src", dataURL);
		dataURL = dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
		$.post("ajax/saveMap.php", { savedMap: dataURL }, function(data) {
			alert('Image Saved to : ' + data);
		});
		$("#debugimg").append($img);
	}
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
	$("#export_map_btn").click(function(ev) {
		ev.preventDefault();
		console.log("clicked");
		$('#map').html2canvas({
			flashcanvas: "js/flashcanvas.min.js",
			proxy: 'proxy.php',
			logging: false,
			profile: false,
			useCORS: true
		});
	});
</script>