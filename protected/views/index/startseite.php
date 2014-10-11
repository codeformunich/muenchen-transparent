<?php
/**
 * @var IndexController $this
 * @var array $geodata
 * @var array $geodata_overflow
 * @var Antrag[] $antraege_stadtrat
 * @var Antrag[] $antraege_sonstige
 * @var string $datum
 * @var bool $explizites_datum
 * @var string $neuere_url_ajax
 * @var string $neuere_url_std
 * @var string $aeltere_url_ajax
 * @var string $aeltere_url_std
 * @var array $statistiken
 */

$this->pageTitle = Yii::app()->name;

?>
<section class="well">
	<h1 class="sr-only"><?=CHtml::encode($this->pageTitle)?></h1>
	<div id="mapholder">
		<div id="map"></div>
	</div>
	<div id="overflow_hinweis" <? if (count($geodata_overflow) == 0) echo "style='visibility: hidden;'"; ?>>
		<label><input type="checkbox" name="zeige_overflow">
			Zeige <span class="anzahl"><?= (count($geodata_overflow) == 1 ? "1 Dokument" : count($geodata_overflow) . " Dokumente") ?></span> mit über 10 Ortsbezügen
		</label>
	</div>
	<div id="benachrichtigung_hinweis">
		<div id="ben_map_infos">
			<div class="nichts" style="font-style: italic;">
				<strong>Hinweis:</strong><br>
				Du kannst dich bei <strong>neuen Dokumenten mit Bezug zu einem bestimmten Ort</strong> per E-Mail benachrichtigen lassen.<br>
				Klicke dazu auf den Ort, bestimme dann den relevanten Radius.<br>
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
			load: ["/js/Leaflet/leaflet.js",
				"/js/Leaflet.Fullscreen/Control.FullScreen.js",
				<?=json_encode($this->getAssetsBase())?> +"/ba_features.js",
				"/js/Leaflet.draw-0.2.3/dist/leaflet.draw.js",
				"/js/leaflet.spiderfy.js",
				"/js/leaflet.textmarkers.js"
			],
			complete: function () {
				var $map = $("#map").AntraegeKarte({
					benachrichtigungen_widget: "benachrichtigung_hinweis",
					show_BAs: true,
					benachrichtigungen_widget_zoom: 14,
					ba_link: "<?=CHtml::encode($this->createUrl("index/ba", array("ba_nr" => "12345")))?>",
					onSelect: function (latlng, rad, zoom) {
						if (zoom >= 14) {
							index_geo_dokumente_load("<?=CHtml::encode($this->createUrl("index/antraegeAjaxGeo"))?>?lng=" + latlng.lng + "&lat=" + latlng.lat + "&radius=" + rad + "&", latlng.lng, latlng.lat, rad);
							$("section.teaser_holder").hide();
						} else {
							$("section.teaser_holder").show();
						}
					}
				});
				$map.AntraegeKarte("setAntraegeData", <?=json_encode($geodata)?>, <?=json_encode($geodata_overflow)?>);
			}
		})
	</script>

	<section class="teaser">
		<div class="row">
			<div class="col-md-12">
				<a href="<?= CHtml::encode(Yii::app()->createUrl("infos/soFunktioniertStadtpolitik")) ?>" class="btn btn-success">
					<h2><span class="glyphicon glyphicon-info-sign"></span>So funktioniert Stadtpolitik</h2>

					<div class="description">
						Kommunalpolitik in München einfach erklärt
					</div>
				</a>
			</div>

		</div>

		<div class="row">
			<div class="col-md-6">
				<a href="<?= CHtml::encode(Yii::app()->createUrl("termine/index")) ?>" class="btn btn-info">
					<h2><span class="glyphicon glyphicon-calendar"></span>Termine</h2>

					<div class="description">
						Wann finden Stadtrats- / Ausschusssitzungen statt?
					</div>
				</a>
			</div>

			<div class="col-md-6">
				<a href="<?= CHtml::encode(Yii::app()->createUrl("index/personen")) ?>" class="btn btn-info">
					<h2><span class="glyphicon glyphicon-user"></span>Personen</h2>

					<div class="description">
						Wer sitzt im Stadtrat / in den Bezirksausschüssen?
					</div>
				</a>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<a href="<?= CHtml::encode(Yii::app()->createUrl("themen/index")) ?>" class="btn btn-info">
					<h2><span class="glyphicon glyphicon-chevron-right"></span>Themen</h2>

					<div class="description">
						Dokumente, gegliedert nach Thema und Referat
					</div>
				</a>
			</div>

			<div class="col-md-6">
				<a href="<?= CHtml::encode(Yii::app()->createUrl("benachrichtigungen/index")) ?>" class="btn btn-info">
					<h2><span class="glyphicon" style="height: 37px; font-weight: bold;">@</span> E-Mail-Benachrichtigung</h2>

					<div class="description">
						Per Mail über neue Dokumente informiert werden
					</div>
				</a>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<a href="<?= CHtml::encode(Yii::app()->createUrl("index/suche")) ?>" class="btn btn-info">
					<h2><span class="glyphicon glyphicon-search"></span> Dokumentensuche</h2>

					<div class="description">
						Durchsuche <?= number_format($statistiken["anzahl_dokumente"], 0, ",", ".") ?> Dokumente
						/ <?= number_format($statistiken["anzahl_seiten"], 0, ",", ".") ?>
						Seiten.
					</div>
				</a>
			</div>

			<div class="col-md-6">
				<a href="<?= CHtml::encode(Yii::app()->createUrl("infos/ueber")) ?>" class="btn btn-info">
					<h2><span class="glyphicon glyphicon-question-sign"></span>Über München-Transparent</h2>

					<div class="description">
						Über diese Seite
					</div>
				</a>
			</div>
		</div>
	</section>

</section>

<section class="well two_cols" id="listen_holder">

	<!--
<small>Neu in den letzten 7 Tagen: <?= number_format($statistiken["anzahl_dokumente_1w"], 0, ",", ".") ?> Dokumente
					(<?= number_format($statistiken["anzahl_seiten_1w"], 0, ",", ".") ?> Seiten)
				</small>
				-->


	<div id="stadtratsdokumente_holder">
		<?
		$this->renderPartial("index_antraege_liste2", array(
			"antraege"          => $antraege_stadtrat,
			"datum"             => $datum,
			"neuere_url_ajax"   => $neuere_url_ajax,
			"neuere_url_std"    => $neuere_url_std,
			"aeltere_url_ajax"  => null,
			"aeltere_url_std"   => null,
			"weiter_links_oben" => $explizites_datum,
		));
		$this->renderPartial("index_antraege_liste2", array(
			"title"             => "Sonstige neue Dokumente",
			"antraege"          => $antraege_sonstige,
			"datum"             => $datum,
			"neuere_url_ajax"   => $neuere_url_ajax,
			"neuere_url_std"    => $neuere_url_std,
			"aeltere_url_ajax"  => $aeltere_url_ajax,
			"aeltere_url_std"   => $aeltere_url_std,
			"weiter_links_oben" => false,
		));
		?>
	</div>
</section>


