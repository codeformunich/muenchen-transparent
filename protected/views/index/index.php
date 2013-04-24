<?php
/** @var IndexController $this */

$this->pageTitle = Yii::app()->name;

$assets_base = $this->getAssetsBase();

/** @var CWebApplication $app */
$app = Yii::app();
/** @var CClientScript $cs */
$cs = $app->getClientScript();

//$cs->registerScriptFile($assets_base . '/js/index.js');
$cs->registerScriptFile('/js/index.js');

/** @var array|Antrag[] $antraege */
$antraege = Antrag::model()->neueste_stadtratsantragsdokumente(24 * 2)->findAll();

$geodata = array();
foreach ($antraege as $ant) {
	foreach ($ant->dokumente as $dokument) {
		foreach ($dokument->orte as $ort) {
			$str = "<div class='antraglink'>" . CHtml::link($ant->betreff, $ant->getLink()) . "</div>";
			$str .= "<div class='ort_dokument'>";
			$str .= "<div class='ort'>" . CHtml::encode($ort->ort->ort) . "</div>";
			$str .= "<div class='dokument'>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</div>";
			$str .= "</div>";
			$geodata[] = array(
				FloatVal($ort->ort->lat),
				FloatVal($ort->ort->lon),
				$str
			);
		}
	}
}
?>

<div id="mapholder">
	<div id="map"></div>
</div>

<script>
	ASSETS_BASE = <?=json_encode($assets_base)?>;
	init_startseite(<?=json_encode($geodata)?>);
</script>


<div class="row">
	<div class="col-span-5">
		<h3>Stadtratsanträge</h3>
		<ul class="antragsliste">
		<?
		foreach ($antraege as $ant) {
			echo "<li><div class='antraglink'>" . CHtml::link($ant->getName(), $ant->getLink()) . "</div>";

			$max_date = 0;
			$doklist = "";
			foreach ($ant->dokumente as $dokument) {
				$doklist .= "<li>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</li>";
				$dat = RISTools::date_iso2timestamp($dokument->datum);
				if ($dat > $max_date) $max_date = $dat;
			}

			echo "<div class='add_meta'>";
			$parteien = array();
			foreach ($ant->antraegePersonen as $person) {
				$name = $person->person->name;
				$partei = $person->person->ratePartei();
				if (!$partei) {
					$parteien[$name] = array($name);
				} else {
					if (!isset($parteien[$partei])) $parteien[$partei] = array();
					$parteien[$partei][] = $person->person->name;
				}
			}

			$p_strs = array();
			foreach ($parteien as $partei => $personen) {
				$str = "<span class='partei' title='" . CHtml::encode(implode(", ", $personen)) . "'>";
				$str .= CHtml::encode($partei);
				$str .= "</span>";
				$p_strs[] = $str;
			}
			if (count($p_strs) > 0) echo implode(", ", $p_strs) . ", ";
			echo date("d.m.", $max_date);
			echo "</div>";

			echo "<ul class='dokumente'>";
			echo $doklist;
			echo "</ul></li>\n";
		}
		?>
		</ul>
	</div>
	<div class="col-span-4">
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
	<div class="col-span-3">
		<h3>Benachrichtigungen</h3>
		<p>
			<a href="<?=CHtml::encode($this->createUrl("index/feed"))?>" class="startseite_benachrichtigung_link" title="RSS-Feed">R</a>
			<a href="#" class="startseite_benachrichtigung_link" title="Twitter">T</a>
			<a href="#" class="startseite_benachrichtigung_link" title="Facebook">f</a>

		</p>

		<h3>Infos</h3>
		<p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
		<p><a class="btn" href="#">View details &raquo;</a></p>
	</div>
</div>
