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
 * @var Termin[] $termine_zukunft
 * @var Termin[] $termine_vergangenheit
 * @var Termin[] $termin_dokumente
 * @var int $tage_zukunft
 * @var int $tage_vergangenheit
 * @var int $tage_vergangenheit_dokumente
 */

$this->layout = "//layouts/width_wide";

/**
 * @var Termin[] $termine
 * @return array[]
 */
function ba_gruppiere_termine($termine)
{
	$data = array();
	foreach ($termine as $termin) {
		$key = $termin->termin . $termin->sitzungsort;
		if (!isset($data[$key])) {
			$ts         = RISTools::date_iso2timestamp($termin->termin);
			$data[$key] = array(
				"id"        => $termin->id,
				"datum"     => strftime("%e. %b., %H:%M", $ts),
				"gremien"   => array(),
				"ort"       => $termin->sitzungsort,
				"tos"       => array(),
				"dokumente" => $termin->antraegeDokumente,
			);
		}
		$url = Yii::app()->createUrl("termine/anzeigen", array("termin_id" => $termin->id));
		if (!isset($data[$key]["gremien"][$termin->gremium->name])) $data[$key]["gremien"][$termin->gremium->name] = array();
		$data[$key]["gremien"][$termin->gremium->name][] = $url;
	}
	foreach ($data as $key => $val) ksort($data[$key]["gremien"]);
	return $data;
}

$this->pageTitle = Yii::app()->name . ": Bezirksausschuss " . $ba->ba_nr . " (" . $ba->name . ")";

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
				Du kannst dich bei <strong>neuen Dokumenten mit Bezug zu einem bestimmten Ort</strong> per E-Mail benachrichtigen lassen.<br>Klicke dazu auf den Ort, bestimme dann
				den
				relevanten Radius.<br>
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
			load: ["/js/Leaflet/leaflet.js", "/js/Leaflet.Fullscreen/Control.FullScreen.js", <?=json_encode($this->getAssetsBase())?> +"/ba_features.js",
				"/js/Leaflet.draw-0.2.3/dist/leaflet.draw.js",
				"/js/leaflet.spiderfy.js",
				"/js/leaflet.textmarkers.js"
			],
			complete: function () {
				var $map = $("#map").AntraegeKarte({
					benachrichtigungen_widget: "benachrichtigung_hinweis",
					benachrichtigungen_widget_zoom: 15,
					outlineBA: <?=$ba->ba_nr?>,
					onSelect: function (latlng, rad, zoom) {
						if (zoom >= 15) {
							index_geo_dokumente_load("", latlng.lng, latlng.lat, rad);
						}
					}
				});
				$map.AntraegeKarte("setAntraegeData", <?=json_encode($geodata)?>, <?=json_encode($geodata_overflow)?>);
			}
		});
	</script>
</section>


<div class="row <? if ($explizites_datum) echo "nur_dokumente"; ?>" id="listen_holder">
	<div class="col col-md-5" id="stadtratsdokumente_holder">
		<div class="well" style="overflow: auto;">
			<? $this->renderPartial("index_antraege_liste2", array(
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
				?>
				<h3>Neue Sitzungsdokumente</h3>
				<ul class="antragsliste"><?
					foreach ($termin_dokumente as $termin) {
						$ts = RISTools::date_iso2timestamp($termin->termin);
						echo "<li class='listitem'><div class='antraglink'>" . CHtml::encode(strftime("%e. %b., %H:%M", $ts) . ", " . $termin->gremium->name) . "</div>";
						foreach ($termin->antraegeDokumente as $dokument) {
							echo "<ul class='dokumente'><li>";
							echo "<div style='float: right;'>" . CHtml::encode(strftime("%e. %b.", RISTools::date_iso2timestamp($dokument->datum))) . "</div>";
							echo CHtml::link($dokument->name, $dokument->getOriginalLink());
							echo "</li></ul>";
						}
						echo "</li>";
					}
					?></ul>
			<? } ?>

			<h3>Kommende <abbr title="Bezirksausschuss - Stadtteil-&quot;Parlament&quot;">BA</abbr>-Termine</h3>
			<?
			$termine_ids = array();

			$data = ba_gruppiere_termine($termine_zukunft);
			if (count($data) == 0) echo "<p class='keine_gefunden'>Keine Termine in den nächsten $tage_zukunft Tagen</p>";
			else $this->renderPartial("../termine/termin_liste", array(
				"termine" => $data
			));
			?>

			<h3>Vergangene <abbr title="Bezirksausschuss - Stadtteil-&quot;Parlament&quot;">BA</abbr>-Termine</h3>
			<?
			$data = ba_gruppiere_termine($termine_vergangenheit);
			if (count($data) == 0) echo "<p class='keine_gefunden'>Keine Termine in den letzten $tage_vergangenheit Tagen</p>";
			else $this->renderPartial("../termine/termin_liste", array(
				"termine" => $data
			));
			?>
		</div>
	</div>

	<div class="col col-md-3 keine_dokumente">
		<section class="well"><?
			$insgesamt = 0;
			foreach ($fraktionen as $fraktion)
				$insgesamt += count($fraktion);
			?>

			<h2>StadträtInnen <span style="float: right"><?= $insgesamt ?></span></h2>

			<ul class="fraktionen_liste"><?
				usort($fraktionen, function ($val1, $val2) {
					if (count($val1) < count($val2)) return 1;
					if (count($val1) > count($val2)) return -1;
					return 0;
				});
				foreach ($fraktionen as $fraktion) {
					/** @var StadtraetIn[] $fraktion */
					$fr = $fraktion[0]->stadtraetInnenFraktionen[0]->fraktion;
					echo "<li><a href='" . CHtml::encode($fr->getLink()) . "' class='name'><span class=\"glyphicon glyphicon-chevron-right\"></span>";
					echo "<span class='count'>" . count($fraktion) . "</span>";
					echo CHtml::encode($fr->name) . "</a><ul class='mitglieder'>";
					foreach ($fraktion as $mitglied) {
						echo "<li>";
						echo "<a href='" . CHtml::encode($mitglied->getLink()) . "' class='ris_link'>"    . CHtml::encode($mitglied->name             ) .                                                            "</a>";
						if ($mitglied->abgeordnetenwatch != "") echo "<a href='"                          . CHtml::encode($mitglied->abgeordnetenwatch) . "' title='Abgeordnetenwatch' class='abgeordnetenwatch_link'></a>";
						if ($mitglied->web               != "") echo "<a href='"                          . CHtml::encode($mitglied->web              ) . "' title='Homepage'          class='web_link'>             </a>";
						if ($mitglied->twitter           != "") echo "<a href='https://twitter.com/"      . CHtml::encode($mitglied->twitter          ) . "' title='Twitter'           class='twitter_link'>T         </a>";
						if ($mitglied->facebook          != "") echo "<a href='https://www.facebook.com/" . CHtml::encode($mitglied->facebook         ) . "' title='Facebook'          class='fb_link'>     f         </a>";
						echo "</li>\n";
					}
					echo "</ul></li>\n";
				}
			?></ul>

			<script>
				$(function () {
					var $frakts = $(".fraktionen_liste > li");
					$frakts.addClass("closed").find("> a").click(function (ev) {
						if (ev.which == 2 || ev.which == 3) return;
						ev.preventDefault();
						var $li = $(this).parents("li").first();
						if ($li.hasClass("closed")) {
							$li.removeClass("closed");
							$li.find(".glyphicon").removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
						} else {
                            $li.addClass("closed");
                            $li.find(".glyphicon").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
                        }
					});
				})
			</script>
		</section>
	</div>

</div>
