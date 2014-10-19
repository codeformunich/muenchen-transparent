<?php
/**
 * @var array $termine_zukunft
 * @var array $termine_vergangenheit
 * @var Termin[] $termin_dokumente
 * @var array $fullcalendar_struct
 * @var int $tage_zukunft
 * @var int $tage_vergangenheit
 */

?>

<section class="well">
	<ul class="breadcrumb" style="margin-bottom: 5px;">
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
		<li class="active">Termine</li>
	</ul>
	<h1>Termine</h1>
</section>

<script src="/js/moment-with-locales.js"></script>
<script src="/js/fullcalendar-2.1.1/fullcalendar.min.js"></script>
<script src="/js/fullcalendar-2.1.1/lang/de.js"></script>
<div class="row" id="listen_holder">
	<div class="col col-md-12">
		<section class="well">
			<div id='calendar'></div>
			<script>
				$(function () {
					$('#calendar').fullCalendar({
						header: {
							left: 'prev,next today',
							center: 'title',
							right: 'month,basicWeek,basicDay'
						},
						eventLimit: true,
						lang: $("html").attr("lang"),
						weekNumbers: true,
						weekends: <?=($fullcalendar_struct["has_weekend"] ? "true" : "false")?>,
						eventSources: [
							"<?=CHtml::encode(Yii::app()->createUrl("termine/fullCalendarFeed"))?>"
						],
						eventRender: function(event, element) {
							element.attr("title", event["title"]);
							console.log(event);
							console.log(element);
						}
					})
				})
			</script>
		</section>
	</div>


	<div class="col col-md-6">
		<div class="well">
			<h3>Kommende Termine</h3>
			<?
			if (count($termine_zukunft) == 0) echo "<p class='keine_gefunden'>Keine Termine in den nächsten $tage_zukunft Tagen</p>";
			else $this->renderPartial("termin_liste", array(
				"termine" => $termine_zukunft
			));
			?>

			<h3>Vergangene Termine</h3>
			<?
			if (count($termine_vergangenheit) == 0) echo "<p class='keine_gefunden'>Keine Termine in den letzten $tage_vergangenheit Tagen</p>";
			else $this->renderPartial("termin_liste", array(
				"termine" => $termine_vergangenheit
			)); ?>
		</div>
	</div>
	<div class="col col-md-6">
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
			<?
			}
			?>
		</div>
	</div>
</div>