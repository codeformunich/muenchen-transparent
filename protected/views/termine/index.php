<?php
/**
 * @var array $termine_zukunft
 * @var array $termine_vergangenheit
 * @var Termin[] $termin_dokumente
 * @var array $fullcalendar_struct
 * @var int $tage_zukunft
 * @var int $tage_vergangenheit
 */
$this->pageTitle = "Termine";
?>

<section class="well">
	<ul class="breadcrumb" style="margin-bottom: 5px;">
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
		<li class="active">Termine</li>
	</ul>
	<h1 class="sr-only">Termine</h1>

	<script src="/js/moment-with-locales.js"></script>
	<script src="/js/fullcalendar-2.1.1/fullcalendar.min.js"></script>
	<script src="/js/fullcalendar-2.1.1/lang/de.js"></script>
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
				eventRender: function (event, element) {
					element.attr("title", event["title"]);
					console.log(event);
					console.log(element);
				}
			})
		})
	</script>
</section>

<div class="row" id="listen_holder">
	<div class="col col-md-6">
		<div class="well">
			<h3>Kommende Termine</h3>
			<br>
			<?
			if (count($termine_zukunft) == 0) echo "<p class='keine_gefunden'>Keine Termine in den nächsten $tage_zukunft Tagen</p>";
			else $this->renderPartial("termin_liste", array(
				"termine"     => $termine_zukunft,
				"gremienname" => true,
			));
			?>

		</div>
	</div>
	<div class="col col-md-6">
		<div class="well">
			<?
			if (count($termin_dokumente) > 0) {
				?>
				<h3>Neue Protokolle</h3>
				<br>
				<ul class="antragsliste2"><?
					foreach ($termin_dokumente as $termin) {
						$titel = $termin->gremium->name . " am " . strftime("%e. %B '%y, %H:%M Uhr", RISTools::date_iso2timestamp($termin->termin));
						echo '<li class="panel panel-primary"><div class="panel-heading"><a href="' . CHtml::encode($termin->getLink()) . '"><span>';
						echo CHtml::encode($titel) . '</a></span></div>';
						echo '<div class="panel-body">';

						$max_date = 0;
						$doklist  = "";
						foreach ($termin->antraegeDokumente as $dokument) {
							//$doklist .= "<li>" . CHtml::link($dokument->name, $this->createUrl("index/dokument", array("id" => $dokument->id))) . "</li>";
							$dokurl = $dokument->getLinkZumDokument();
							$doklist .= "<li><a href='" . CHtml::encode($dokurl) . "'";
							if (substr($dokurl, strlen($dokurl) - 3) == "pdf") $doklist .= ' class="pdf"';
							$doklist .= ">" . CHtml::encode($dokument->name) . "</a></li>";
							$dat = RISTools::date_iso2timestamp($dokument->getDate());
							if ($dat > $max_date) $max_date = $dat;
						}
						echo "<ul class='dokumente'>";
						echo $doklist;
						echo "</ul></div></li>\n";

					}
					?></ul>

				<br>
				<h3>Vergangene Termine</h3>
				<br>
				<?
				if (count($termine_vergangenheit) == 0) echo "<p class='keine_gefunden'>Keine Termine in den letzten $tage_vergangenheit Tagen</p>";
				else $this->renderPartial("termin_liste", array(
					"termine"     => $termine_vergangenheit,
					"gremienname" => true,
				)); ?>

			<?
			}
			?>
		</div>
	</div>
</div>