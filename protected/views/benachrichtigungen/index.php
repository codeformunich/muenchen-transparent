<?php
/**
 * @var $this IndexController
 * @var $error array
 * @var string $code
 * @var string $message
 * @var BenutzerIn $ich
 * @var string $msg_ok
 * @var string $msg_err
 */

$this->pageTitle = "Benachrichtigungen";

$assets_base = $this->getAssetsBase();
?>

<h1>Benachrichtigungen</h1>


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

?>

<h3 style="margin-top: 4em;">E-Mail-Benachrichtigung an <?= CHtml::encode($ich->email) ?>:</h3>
<?
$bens = $ich->getBenachrichtigungen();
if (count($bens) == 0) {
	?>
	<div class="benachrichtigung_keine col col-lg-5 well">Noch keine Benachrichtigungen</div>
<?
} else {
	?>
	<form method="POST" action="<?= CHtml::encode($this->createUrl("index/benachrichtigungen")) ?>">
		<ul class="benachrichtigungsliste">
			<li class="header">
				<div class="del_holder">Löschen</div>
				<div class="krit_holder">Suchkriterium</div>
				<div class="such_holder">Suchen</div>
			</li>
			<?
			foreach ($bens as $ben) {
				$del_form_name = AntiXSS::createToken("del_ben") . "[" . RISTools::bracketEscape(CHtml::encode(json_encode($ben->krits))) . "]";
				$such_url      = $ben->getUrl();
				?>
				<li>
					<div class='del_holder'>
						<button type='submit' class='del' name='<?= $del_form_name ?>'><span class='glyphicon glyphicon-minus-sign'></span></button>
					</div>
					<div class='krit_holder'><?= $ben->getTitle() ?></div>
					<div class='such_holder'><a href='<?= RISTools::bracketEscape(CHtml::encode($ben->getUrl())) ?>'><span class='glyphicon glyphicon-search'></span></a></div>
				</li>
			<?
			}
			?>
		</ul>
	</form>

	<div class="ben_alle_holder">
		<a href="<?= CHtml::encode($this->createUrl("benachrichtigungen/alleSuchergebnisse")) ?>" class="ben_alle_suche"><span class="glyphicon glyphicon-chevron-right"></span> Alle Suchergebnisse</a>
		<a href="<?= CHtml::encode($this->createUrl("benachrichtigungen/alleFeed", array("code" => $ich->getFeedCode()))) ?>" class="ben_alle_feed"><span class="icon-rss"></span> Alle Suchergebnisse als Feed</a>
	</div>
<? } ?>

<br style="clear: both;">

<form method="POST" action="<?= CHtml::encode($this->createUrl("index/benachrichtigungen")) ?>" class="benachrichtigung_add">
	<fieldset>
		<legend>Benachrichtige mich bei neuen Dokumenten...</legend>

		<label for="suchbegriff"><span class="glyphicon glyphicon-search"></span> <span class="name">mit diesem Suchbegriff:</span></label><br>

		<div class="input-group col col-lg-5">
			<input type="text" placeholder="Suchbegriff" id="suchbegriff" name="suchbegriff">
  			<span class="input-group-btn">
    			<button class="btn btn-primary" name="<?= AntiXSS::createToken("ben_add_text") ?>" type="submit">Benachrichtigen!</button>
  			</span>
		</div>
	</fieldset>
</form>


<br style="clear: both; ">
<hr style="margin-top: 1.5em; margin-bottom: 1.5em;">

<form method="POST" action="<?= CHtml::encode($this->createUrl("index/benachrichtigungen")) ?>" class="benachrichtigung_add" id="benachrichtigung_add_geo_form">
	<fieldset>
		<label for="geo_radius"><span class="glyphicon glyphicon-map-marker"></span> <span class="name">mit diesem Ortsbezug:</span></label>

		<br style="clear: both;">

		<div id="ben_mapholder" class="col col-lg-5">
			<div id="ben_map"></div>
		</div>
		<div id="ben_map_infos">
			<div class="nichts" style="font-style: italic;">noch nichts ausgewählt</div>
			<div class="infos" style="display: none;">
				Ausgewählter Radius: <span class="radius_m"></span> Meter
			</div>
			<input type="hidden" name="geo_lng" value="">
			<input type="hidden" name="geo_lat" value="">
			<input type="hidden" name="geo_radius" id="geo_radius" value="">

			<div style="margin-top: 20px;">
				<button class="btn btn-primary ben_add_geo" disabled name="<?= AntiXSS::createToken("ben_add_geo") ?>" type="submit">Benachrichtigen!</button>
			</div>
		</div>

	</fieldset>

	<script>
		ASSETS_BASE = <?=json_encode($assets_base)?>;
		yepnope({
			load: ["/js/Leaflet/leaflet.js", "/js/leaflet.fullscreen/Control.FullScreen.js", "/js/Leaflet.draw/dist/leaflet.draw.js"],
			complete: function () {
				var $ben_holder = $("#ben_map_infos");
				$("#ben_map").AntraegeKarte({ benachrichtigungen_widget: true, show_BAs: false, benachrichtigungen_widget_zoom: 9, onSelect: function (latlng, rad) {
					$ben_holder.find(".nichts").hide();
					$ben_holder.find(".infos").show();
					$(".ben_add_geo").prop("disabled", false);

					$ben_holder.find("input[name=geo_lng]").val(latlng.lng);
					$ben_holder.find("input[name=geo_lat]").val(latlng.lat);
					$ben_holder.find("input[name=geo_radius]").val(rad);

					$ben_holder.find(".radius_m").text(parseInt(rad));
				}});
			}
		});
	</script>

</form>
