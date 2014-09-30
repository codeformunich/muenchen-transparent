<?php
/**
 * @var Termin[] $termine_zukunft
 * @var Termin[] $termine_vergangenheit
 * @var Termin[] $termin_dokumente
 * @var int $tage_zukunft
 * @var int $tage_vergangenheit
 */

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

?>

<h1>Termine</h1>
<a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>"><span class="glyphicon glyphicon-arrow-left"></span> Zurück</a><br>

<div class="row" id="listen_holder">
	<div class="col col-lg-6">

		<h3>Kommende Termine</h3>
		<?
		$data = gruppiere_termine($termine_zukunft);
		if (count($data) == 0) echo "<p class='keine_gefunden'>Keine Termine in den nächsten $tage_zukunft Tagen</p>";
		else $this->renderPartial("termin_liste", array(
			"termine" => $data
		));
		?>

		<h3>Vergangene Termine</h3>
		<?
		$data = gruppiere_termine($termine_vergangenheit);
		if (count($data) == 0) echo "<p class='keine_gefunden'>Keine Termine in den letzten $tage_vergangenheit Tagen</p>";
		else $this->renderPartial("termin_liste", array(
			"termine" => $data
		)); ?>
	</div>
	<div class="col col-lg-6">
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
		<?
		}
		?>
	</div>
</div>