<?php
/**
 * @var IndexController $this
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 * @var RISSucheKrits $krits
 * @var string $msg_ok
 * @var string $msg_err
 * @var bool $email_bestaetigt
 * @var bool $email_angegeben
 * @var bool $eingeloggt
 * @var bool $wird_benachrichtigt
 * @var BenutzerIn $ich
 * @var null|array $geodata
 * @var null|array $geodata_overflow
 */

$this->pageTitle = Yii::app()->name;

?>
	<div class="box" style="font-size: 18px; float: right; border: 1px solid #E1E1E8; border-radius: 4px; padding: 5px; background-color: #eee; overflow: hidden;">
		<a href="<?= CHtml::encode($krits->getFeedUrl()) ?>"><span class="icon-rss"></span> Suchergebnisse als RSS-Feed</a>
	</div>

	<h1><span style="font-weight: bold;">Suchergebnisse:</span> <?= CHtml::encode($krits->getTitle()) ?></h1>

<?


if ($msg_ok != "") {
	?>
	<div class="alert alert-success">
		<?php echo $msg_ok; ?>
	</div>
<?
}
if ($msg_err != "") {
	?>
	<div class="alert alert-error">
		<?php echo $msg_err; ?>
	</div>
<?
}


if (!is_null($geodata) && count($geodata) > 0) {
	$assets_base = $this->getAssetsBase();
	$geokrit     = $krits->getGeoKrit();
	?>
	<div id="mapholder">
		<div id="map"></div>
	</div>
	<div id="overflow_hinweis" <? if (count($geodata_overflow) == 0) echo "style='display: none;'"; ?>><label><input type="checkbox" name="zeige_overflow"> Zeige <span
				class="anzahl"><?= (count($geodata_overflow) == 1 ? "1 Dokument" : count($geodata_overflow) . " Dokumente") ?></span> mit über 20 Ortsbezügen</label></div>

	<script>
		yepnope({
			load: ["/js/Leaflet/leaflet.js", "/js/leaflet.fullscreen/Control.FullScreen.js", <?=json_encode($assets_base)?> +"/ba_features.js"],
			complete: function () {
				var $map = $("#map").AntraegeKarte({
					lat: <?=$geokrit["lat"]?>,
					lng: <?=$geokrit["lng"]?>,
					size: 14
				});
				$map.AntraegeKarte("setAntraegeData", <?=json_encode($geodata)?>, <?=json_encode($geodata_overflow)?>);
			}
		});
	</script>
<?
}

$facet_groups = array();

$antrag_typ = array();
$facet = $ergebnisse->getFacetSet()->getFacet('antrag_typ');
foreach ($facet as $value => $count) if ($count > 0) {
	$str = "<li><a href='" . RISTools::bracketEscape(CHtml::encode($krits->cloneKrits()->addAntragTypKrit($value)->getUrl())) . "'>";
	if (isset(Antrag::$TYPEN_ALLE[$value])) {
		$x = explode("|", Antrag::$TYPEN_ALLE[$value]);
		$str .= $x[1] . ' (' . $count . ')';
	} elseif ($value == "stadtrat_termin") $str .= 'Stadtrats-Termin (' . $count . ')';
	elseif ($value == "ba_termin") $str .= 'BA-Termin (' . $count . ')';
	else $str .= $value . " (" . $count . ")";
	$str .= "</a></li>";
	$antrag_typ[] = $str;
}
if (count($antrag_typ) > 0) $facet_groups["Dokumenttypen"] = $antrag_typ;

$wahlperiode = array();
$facet = $ergebnisse->getFacetSet()->getFacet('antrag_wahlperiode');
foreach ($facet as $value => $count) if ($count > 0) {
	$str = "<li><a href='" . RISTools::bracketEscape(CHtml::encode($krits->cloneKrits()->addWahlperiodeKrit($value)->getUrl())) . "'>";
	$str .= $value . ' (' . $count . ')';
	$str .= "</a></li>";
	$wahlperiode[] = $str;
}
if (count($wahlperiode) > 0) $facet_groups["Wahlperiode"] = $wahlperiode;

?>
	<div class="suchergebnis_titlebox_holder">
		<div class="box">
			<?
			if (count($facet_groups) > 0) {
				?>
				<h2>Ergebnisse einschränken</h2>
				<ul class="filterlist">
					<?
					foreach ($facet_groups as $name => $facets) if (count($facets) > 1) {
						echo "<li><h3>" . CHtml::encode($name) . "</h3><ul>";
						echo implode("", $facets);
						echo "</ul></li>";
					}
					?></ul>
				<br>
			<?
			}
			?>
		</div>
	</div>

<? $this->renderPartial("suchergebnisse_benachrichtigungen", array(
	"eingeloggt"          => $eingeloggt,
	"email_angegeben"     => $email_angegeben,
	"email_bestaetigt"    => $email_bestaetigt,
	"wird_benachrichtigt" => $wird_benachrichtigt,
	"ich"                 => $ich,
	"krits"               => $krits
));

if ($krits->getKritsCount() > 0) $this->renderPartial("../benachrichtigungen/suchergebnisse_liste", array(
	"ergebnisse" => $ergebnisse,
));
