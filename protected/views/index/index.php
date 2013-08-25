<?php
/**
 * @var IndexController $this
 * @var array $geodata
 * @var Antrag[] $antraege
 * @var string $datum
 * @var string $weitere_url
 * @var Termin[] $termine_zukunft
 * @var Termin[] $termine_vergangenheit
 * @var Termin[] $termin_dokumente
 * @var int $tage_zukunft
 * @var int $tage_vergangenheit
 */

$this->pageTitle = Yii::app()->name;

$assets_base = $this->getAssetsBase();

/** @var CWebApplication $app */
$app = Yii::app();
/** @var CClientScript $cs */
$cs = $app->getClientScript();

//$cs->registerScriptFile($assets_base . '/js/index.js');
$cs->registerScriptFile('/js/index.js');


/**
 * @var Termin[] $termine
 * @return array[]
 */
function gruppiere_termine($termine)
{
	$data = array();
	foreach ($termine as $termin) {
		$key = $termin->termin . $termin->sitzungsort;
		if (!isset($data[$key])) {
			$ts         = RISTools::date_iso2timestamp($termin->termin);
			$data[$key] = array(
				"datum"   => date("j.M, H:i", $ts),
				"gremien" => array(),
				"ort"     => $termin->sitzungsort,
				"tos"     => array(),
			);
		}
		$url = "http://www.ris-muenchen.de/RII2/RII/ris_sitzung_detail.jsp?risid=" . $termin->id;
		if (!isset($data[$key]["gremien"][$termin->gremium->name])) $data[$key]["gremien"][$termin->gremium->name] = array();
		$data[$key]["gremien"][$termin->gremium->name][] = $url;
	}
	foreach ($data as $key => $val) ksort($data[$key]["gremien"]);
	return $data;
}

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
		load: ["/js/Leaflet/leaflet.js", "/js/leaflet.fullscreen/Control.FullScreen.js", <?=json_encode($assets_base)?> +"/ba_features.js",
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
		<?
		$data = gruppiere_termine($termine_zukunft);
		if (count($data) == 0) echo "<p class='keine_gefunden'>Keine Termine in den nächsten $tage_zukunft Tagen</p>";
		else {
			?>
			<ul class="terminliste"><?

				foreach ($data as $termin) {
					echo "<li><div class='termin'>" . CHtml::encode($termin["datum"] . ", " . $termin["ort"]) . "</div><div class='termindetails'>";
					$gremien = array();
					foreach ($termin["gremien"] as $name => $links) {
						foreach ($links as $link) $gremien[] = CHtml::link($name, $link);
					}
					echo implode(", ", $gremien);
					echo "</div></li>";
				}
				?></ul>
		<? } ?>

		<h3>Vergangene Termine</h3>
		<?
		$data = gruppiere_termine($termine_vergangenheit);
		if (count($data) == 0) echo "<p class='keine_gefunden'>Keine Termine in den letzten $tage_vergangenheit Tagen</p>";
		else {
			?>
			<ul class="terminliste"><?
				foreach ($data as $termin) {
					echo "<li><div class='termin'>" . CHtml::encode($termin["datum"] . ", " . $termin["ort"]) . "</div><div class='termindetails'>";
					$gremien = array();
					foreach ($termin["gremien"] as $name => $links) {
						if (count($links) == 1) $gremien[] = CHtml::link($name, $links[0]);
						else {
							$str = CHtml::encode($name);
							for ($i = 0; $i < count($links); $i++) $str .= " [" . CHtml::link($i + 1, $links[$i]) . "]";
							$gremien[] = $str;
						}
					}
					echo implode(", ", $gremien);
					echo "</div></li>";
				}
				?></ul>
		<? } ?>

		<h3>Neue Sitzungsdokumente</h3>
		<?
		if (count($termin_dokumente) == 0) echo "<p class='keine_gefunden'>Keine neue Stadtratsdokumente den letzten $tage_vergangenheit Tagen</p>";
		else {
			?>
			<ul class="antragsliste"><?
				foreach ($termin_dokumente as $termin) {
					$ts = RISTools::date_iso2timestamp($termin->termin);
					echo "<li><div class='antraglink'>" . CHtml::encode(date("j.M, H:i", $ts) . ", " . $termin->gremium->name) . "</div>";
					foreach ($termin->antraegeDokumente as $dokument) {
						echo "<ul class='dokumente'><li>";
						echo "<div style='float: right;'>" . CHtml::encode(date("j.M", RISTools::date_iso2timestamp($dokument->datum))) . "</div>";
						echo CHtml::link($dokument->name, $dokument->getOriginalLink());
						echo "</li></ul>";
					}
					echo "</li>";
				}
				?></ul>
		<? } ?>
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
