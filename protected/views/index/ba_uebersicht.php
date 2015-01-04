<?php

/**
 * @var IndexController $this
 * @var Bezirksausschuss $ba
 * @var Antrag[] $antraege
 * @var string|null $aeltere_url_ajax
 * @var string|null $aeltere_url_std
 * @var string|null $neuere_url_ajax
 * @var string|null $neuere_url_std
 * @var bool $explizites_datum
 * @var array $geodata
 * @var array $geodata_overflow
 * @var string $datum_von
 * @var string $datum_bis
 * @var array $termine
 * @var Termin[] $termin_dokumente
 * @var Fraktion $fraktionen
 * @var int $tage_zukunft
 * @var int $tage_vergangenheit
 * @var int $tage_vergangenheit_dokumente
 * @var Gremium[] $gremien
 */

$this->layout = "//layouts/width_wide";

$this->pageTitle = "Bezirksausschuss " . $ba->ba_nr . ", " . $ba->name;

?>


<section class="well">
	<h1><?= CHtml::encode($ba->name) ?>
		<small>(Bezirksausschuss <?= $ba->ba_nr ?>)</small>
	</h1>


	<div id="mapholder">
		<div id="map"></div>
	</div>
	<div id="overflow_hinweis" <? if (count($geodata_overflow) == 0) echo "style='display: none;'"; ?>>
		<label><input type="checkbox" name="zeige_overflow">
			Zeige <span class="anzahl"><?= (count($geodata_overflow) == 1 ? "1 Dokument" : count($geodata_overflow) . " Dokumente") ?></span> mit über 20 Ortsbezügen
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
		$(function () {
			var $map = $("#map").AntraegeKarte({
				benachrichtigungen_widget: "benachrichtigung_hinweis",
				benachrichtigungen_widget_zoom: 15,
				outlineBA: <?=$ba->ba_nr?>,
				assetsBase: <?=json_encode($this->getAssetsBase())?>,
				onSelect: function (latlng, rad, zoom) {
					if (zoom >= 15) {
						index_geo_dokumente_load("", latlng.lng, latlng.lat, rad);
					}
				},
				onInit: function () {
					$map.AntraegeKarte("setAntraegeData", <?=json_encode($geodata)?>, <?=json_encode($geodata_overflow)?>);
				}
			});
		});
	</script>
</section>


<div class="row <? if ($explizites_datum) echo "nur_dokumente"; ?>" id="listen_holder">
	<div class="col col-md-5" id="stadtratsdokumente_holder">
		<div class="well" style="overflow: auto;">
			<? $this->renderPartial("index_antraege_liste", array(
				"aeltere_url_ajax"  => $aeltere_url_ajax,
				"aeltere_url_std"   => $aeltere_url_std,
				"neuere_url_ajax"   => $neuere_url_ajax,
				"neuere_url_std"    => $neuere_url_std,
				"antraege"          => $antraege,
				"datum_von"         => $datum_von,
				"datum_bis"         => $datum_bis,
				"title"             => ($explizites_datum ? null : "Dokumente der letzten $tage_vergangenheit_dokumente Tage"),
				"weitere_url"       => null,
				"weiter_links_oben" => $explizites_datum,
			)); ?>
		</div>
	</div>
	<div class="col col-md-4 keine_dokumente">
		<div class="well">
			<?
			if (count($termin_dokumente) > 0) {
				/** @var Dokument[] $dokumente */
				$dokumente = array();
				foreach ($termin_dokumente as $termin) {
					foreach ($termin->antraegeDokumente as $dokument) {
						$dokumente[] = $dokument;
					}
				}
				usort($dokumente, function ($dok1, $dok2) {
					/** @var Dokument $dok1 */
					/** @var Dokument $dok2 */
					$ts1 = RISTools::date_iso2timestamp($dok1->getDate());
					$ts2 = RISTools::date_iso2timestamp($dok2->getDate());
					if ($ts1 > $ts2) return -1;
					if ($ts1 < $ts2) return 1;
					return 0;
				});
				?>
				<h3>Protokolle &amp; Tagesordnungen</h3>
				<br>
				<ul class="dokumentenliste_small">
					<? foreach ($dokumente as $dokument) {
						$name = str_replace(" (oeff)", "", $dokument->name);
						$name .= " zur Sitzung am " . date("d.m.Y", RISTools::date_iso2timestamp($dokument->termin->termin));
						echo '<li>';
						echo "<div class='add_meta'>" . CHtml::encode($dokument->getDisplayDate()) . "</div>";
						echo CHtml::link('<span class="glyphicon glyphicon-file"></span> ' . $name, $dokument->getLinkZumDokument());
						echo '</li>';
					} ?>
				</ul>
			<? } ?>

			<br>

			<h3><abbr title="Bezirksausschuss - Stadtteil-&quot;Parlament&quot;">BA</abbr>-Termine</h3>
			<br>
			<?
			$this->renderPartial("../termine/termin_liste", array(
				"termine"     => $termine,
				"gremienname" => false,
			));
			?>
		</div>
	</div>

	<div class="col col-md-3 keine_dokumente"><?
		$this->renderPartial("../personen/fraktionen", array(
			"fraktionen" => $fraktionen,
			"title"      => "BA-Mitglieder",
		));
		$this->renderPartial("../personen/ausschuss_mitglieder", array(
			"gremien" => $gremien,
			"title"   => "Unterausschüsse",
		));
		?>
	</div>

</div>
