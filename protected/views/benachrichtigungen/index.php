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
		<a href="<?=CHtml::encode($this->createUrl("benachrichtigungen/alleSuchergebnisse"))?>" class="ben_alle_suche"><span class="glyphicon glyphicon-chevron-right"></span> Alle Suchergebnisse</a>
		<a href="<?=CHtml::encode($this->createUrl("benachrichtigungen/alleFeed", array("code" => $ich->getFeedCode())))?>" class="ben_alle_feed"><span class="icon-rss"></span> Alle Suchergebnisse als Feed</a>
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

<form method="POST" action="<?= CHtml::encode($this->createUrl("index/benachrichtigungen")) ?>" class="benachrichtigung_add">
	<fieldset>
		<label for="geo_input"><span class="glyphicon glyphicon-map-marker"></span> <span class="name">mit diesem Ortsbezug:</span></label>

		<input type="text" id="geo_input" value="" name="geo_input" style="display: none;">
		<br style="clear: both;">

		<div id="ben_mapholder" class="col col-lg-5">
			<small>(setze zuerst eine Markierung an den Ort, und wähle dann den Radius)</small>
			<div id="ben_map"></div>
		</div>

	</fieldset>

	<script>
		ASSETS_BASE = <?=json_encode($assets_base)?>;
		yepnope({
			load: ["/js/Leaflet/dist/leaflet.js", "/js/leaflet.fullscreen/Control.FullScreen.js"],
			complete: function () {
				var map = L.map('ben_map').setView([48.155, 11.55820], 11),
					L_style = (typeof(window["devicePixelRatio"]) != "undefined" && window["devicePixelRatio"] > 1 ? "997@2x" : "997"),
					fullScreen = new L.Control.FullScreen();
				map.addControl(fullScreen);
				L.tileLayer('http://{s}.tile.cloudmade.com/2f8dd15a9aab49f9aa53f16ac3cb28cb/' + L_style + '/256/{z}/{x}/{y}.png', {
					attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
					maxZoom: 18,
					detectRetina: true
				}).addTo(map);
			}
		});
	</script>

</form>