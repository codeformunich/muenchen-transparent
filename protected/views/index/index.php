<?php
/**
 * @var IndexController $this
 * @var array $geodata
 * @var Antrag[] $antraege
 * @var string $datum
 * @var string $datum_pre
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

<script>
	ASSETS_BASE = <?=json_encode($assets_base)?>;
	init_startseite(<?=json_encode($geodata)?>);
</script>


<div class="row">
	<div class="col col-lg-5" id="stadtratsdokumente_holder">
		<? $this->renderPartial("index_antraege_liste", array(
			"antraege"  => $antraege,
			"datum"     => $datum,
			"datum_pre" => $datum_pre,
			"geodata"   => $geodata,
		)); ?>
	</div>
	<div class="col col-lg-4">
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
	<div class="col col-lg-3">
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
