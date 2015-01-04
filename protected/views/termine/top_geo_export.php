<?php
/**
 * @var Termin $termin
 */

$this->pageTitle = $termin->getName(true);
$assets_base = $this->getAssetsBase();

foreach ($termin->tagesordnungspunkte as $ergebnis) {
	$geo = $ergebnis->get_geo();
	foreach ($geo as $g) $geodata[] = array(
		FloatVal($g->lat),
		FloatVal($g->lon),
		str_replace(".", ".<br>", trim($ergebnis->top_nr, "."))
	);
}
?>
<html>
<head>
	<link rel="stylesheet" href="/js/Leaflet/leaflet.css"/>
	<link rel="stylesheet" href="/css/jquery-ui-1.11.0.custom.min.css"/>
	<style>

		.leaflet-popup-content-wrapper {
			border-radius: 10px;
		}

		.leaflet-popup .ort {
			float: right;
		}

		.leaflet-popup .ort_dokument {
			border-top: solid 1px #999;
			margin-top: 6px;
			padding-top: 6px;
		}

		.leaflet-marker-textmarker .text {
			position: absolute;
			z-index: 203;
			font-size: 13px;
			line-height: 12px;
			top: 3px;
			left: 0;
			width: 24px;
			text-align: center;
			color: white;
			font-weight: bold;
			text-shadow: 0 0 4px yellow;
			filter: dropshadow(color=yellow, offx=0, offy=0);
		}


	</style>

	<script src="/js/jquery-2.1.3.min.js"></script>
	<script src="/js/jquery-ui-1.11.2.custom.min.js"></script>
	<script src="/js/Leaflet/leaflet.js"></script>
	<script src="/js/Leaflet.Control.Geocoder/Control.Geocoder.js"></script>
	<script src="/js/html2canvas.min.js"></script>
	<script src="<?= CHtml::encode($assets_base) ?>/ba_features.js"></script>
	<script src="/js/leaflet.spiderfy.js"></script>
	<script src="/js/Leaflet.Fullscreen/Control.FullScreen.js"></script>
	<script src="/js/leaflet.textmarkers_top.js"></script>
	<script src="/js/antraegekarte.jquery.js"></script>
</head>
<body>
<div id="mapholder">
	<div id="map" style="width: 2600px; height: 1900px;"></div>
</div>
<div id="debugimg"></div>


<script>
	var $map = $("#map").AntraegeKarte({
		outlineBA: <?=($termin->ba_nr > 0 ? $termin->ba_nr : 0)?>,
		textMarkerClass: "TextMarkersTOP"
	});
	$map.AntraegeKarte("setAntraegeDataTOPs", <?=json_encode($geodata)?>, <?=$termin->ba_nr?>);

	window.setTimeout(function() {
		html2canvas($map[0], {
			ignoreClear: true,
			onrendered: function(canvas) {
				var $img = $("<img>");
				$img.attr("src", canvas.toDataURL("image/png"));
				$("#debugimg").append($img);
				//$("#mapholder").remove();
			}
		})
	}, 3000);

</script>
</body>
</html>
