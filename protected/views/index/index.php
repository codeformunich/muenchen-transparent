<?php
/**
 * @var IndexController $this
 * @var array $geodata
 * @var Antrag[] $antraege
 * @var string $datum
 * @var string $weitere_url
 */

$this->pageTitle = Yii::app()->name;

$assets_base = $this->getAssetsBase();

/** @var CWebApplication $app */
$app = Yii::app();
/** @var CClientScript $cs */
$cs = $app->getClientScript();

//$cs->registerScriptFile($assets_base . '/js/index.js');
$cs->registerScriptFile('/js/index.js');


?>

<div id="mapholder">
	<div id="map"></div>
</div>
<div id="benachrichtigung_hinweis">
	<div id="ben_map_infos">
		<div class="nichts" style="font-style: italic;">
			<strong>Hinweis:</strong><br>
			Du kannst dich bei <strong>neuen Dokumenten mit Bezug zu einem bestimmten Ort</strong> per E-Mail benachrichtigen lassen.<br>Klicke dazu auf den Ort, bestimme dann den relevanten Radius.<br>
			<br>
		</div>
		<div class="infos" style="display: none;">
			<strong>Ausgewählt:</strong> <span class="radius_m"></span> Meter um "<span class="zentrum_ort"></span>" (ungefähr)<br>
			<br>Willst du per E-Mail benachrichtigt werden, wenn neue Dokumente mit diesem Ortsbezug erscheinen?
		</div>
		<form method="POST" action="<?= CHtml::encode($this->createUrl("benachrichtigungen/index")) ?>">
			<input type="hidden" name="geo_lng" value="">
			<input type="hidden" name="geo_lat" value="">
			<input type="hidden" name="geo_radius" id="geo_radius" value="">
			<input type="hidden" name="krit_str" value="">

			<div>
				<button class="btn btn-primary ben_add_geo" disabled name="<?= AntiXSS::createToken("ben_add_geo") ?>" type="submit">Benachrichtigen!</button>
			</div>
		</form>
	</div>
</div>

<script>
	yepnope({
		load: ["/js/Leaflet/dist/leaflet.js", "/js/leaflet.fullscreen/Control.FullScreen.js", <?=json_encode($assets_base)?> +"/ba_features.js",
			"/js/Leaflet.draw/dist/leaflet.draw.js",
			"/js/OverlappingMarkerSpiderfier-Leaflet/oms.min.js",
			"/js/leaflet.textmarkers.js"
		],
		complete: function () {
			var $map = $("#map").AntraegeKarte({
				benachrichtigungen_widget: "benachrichtigung_hinweis",
				show_BAs: true,
				benachrichtigungen_widget_zoom: 14,
				onSelect: function (latlng, rad, zoom) {
					if (zoom >= 14) index_geo_dokumente_load("<?=CHtml::encode($this->createUrl("index/antraegeAjaxGeo"))?>?lng=" + latlng.lng + "&lat=" + latlng.lat + "&radius=" + rad + "&", latlng.lng, latlng.lat, rad);
				}
			});
			$map.AntraegeKarte("setAntraegeData", <?=json_encode($geodata)?>);
		}
	})
</script>

<div class="row">
	<div class="col col-lg-5" id="stadtratsdokumente_holder">
		<? $this->renderPartial("index_antraege_liste", array(
			"antraege"    => $antraege,
			"datum"       => $datum,
			"weitere_url" => $weitere_url,
		)); ?>
	</div>
	<div class="col col-lg-4 keine_dokumente">
		<h3>Kommende Termine</h3>
		<ul>
			<li>13.04.2013: ......</li>
			<li>13.04.2013: ......</li>
			<li>13.04.2013: ......</li>
			<li>13.04.2013: ......</li>
			<li>13.04.2013: ......</li>
			<li>13.04.2013: ......</li>
		</ul>

		<h3>Vergangene Termine</h3>
		<ul>
			<li>13.04.2013: ......</li>
			<li>13.04.2013: ......</li>
			<li>13.04.2013: ......</li>
			<li>13.04.2013: ......</li>
			<li>13.04.2013: ......</li>
			<li>13.04.2013: ......</li>
		</ul>
	</div>
	<div class="col col-lg-3 keine_dokumente">
		<h3>Benachrichtigungen</h3>

		<p>
			<a href="<?= CHtml::encode($this->createUrl("index/feed")) ?>" class="startseite_benachrichtigung_link" title="RSS-Feed">R</a>
			<a href="#" class="startseite_benachrichtigung_link" title="Twitter">T</a>
			<a href="#" class="startseite_benachrichtigung_link" title="Facebook">f</a>

		</p>

		<h3>Infos</h3>

		<p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>

		<p><a class="btn" href="#">View details &raquo;</a></p>
	</div>
</div>
