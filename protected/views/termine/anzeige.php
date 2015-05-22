<?php
/**
 * @var TermineController $this
 * @var Termin $termin
 * @var null|Dokument $to_pdf
 * @var null|Tagesordnungspunkt[] $to_db
 */

$this->pageTitle = $termin->getName(true);
$assets_base     = $this->getAssetsBase();
$geodata         = array();

function zeile_anzeigen($feld, $name, $callback)
{
	if (count($feld) == 0) {
		return;
	} else if (count($feld) == 1) {
		?>
		<tr>
			<th><? echo $name ?></th>
			<td>
				<? $callback($feld[0]); ?>
			</td>
		</tr> <?
	} else {
		?>
		<tr>
			<th><? echo $name ?></th>
			<td>
				<ul>
					<? foreach ($feld as $element) {
						?>
						<li> <?
							$callback($element);
							?> </li> <?
					} ?>
				</ul>
			</td>
		</tr> <?
	}
}

?>
<section class="well pdfjs_long" itemscope itemtype="http://schema.org/Event">
	<div class="original_ris_link"><?
		echo CHtml::link("<span class='fontello-right-open'></span> Original-Seite im RIS", $termin->getSourceLink());
		?></div>
	<h1 itemprop="name"><?= CHtml::encode($termin->getName()) ?></h1>
	<br>

	<?
	if ($termin->termin_next_id > 0 || $termin->termin_prev_id > 0) {
		echo '<div style="text-align: center; overflow: auto;">';
		if ($termin->termin_next_id > 0) {
			$url = Yii::app()->createUrl("termine/anzeigen", array("termin_id" => $termin->termin_next_id));
			echo '<a href="' . CHtml::encode($url) . '" style="float: right;">Nächster Termin <span class="fontello-right-open"></span></a>';
		}
		if ($termin->termin_prev_id > 0) {
			$url = Yii::app()->createUrl("termine/anzeigen", array("termin_id" => $termin->termin_prev_id));
			echo '<a href="' . CHtml::encode($url) . '" style="float: left;"><span class="fontello-left-open"></span> Voriger Termin</a>';
		}
		echo '<a href="' . CHtml::encode(Yii::app()->createUrl("termine/aboInfo", array("termin_id" => $termin->id))) . '">Exportieren / Abonnieren</a>';
		echo '</div>';
	}
	?>
	<table class="table termindaten">
		<tbody>
		<tr>
			<th>Datum:</th>
			<td>
				<?= RISTools::datumstring($termin->termin) . ", " . substr($termin->termin, 11, 5) ?>
				<meta itemprop="startdate" content="<?=CHtml::encode($termin->termin)?>">
			</td>
		</tr>
		<? if ($termin->sitzungsstand != "") { ?>
			<tr>
				<th>Sitzungsstand:</th>
				<td <? if ($termin->istAbgesagt()) echo ' class="abgesagt"';?>> <?=CHtml::encode($termin->sitzungsstand)?></td>
			</tr>
		<? } ?>
		<tr>
			<th>Ort:</th>
			<td itemprop="location">
				<?= CHtml::encode($termin->sitzungsort) ?>
			</td>
		</tr>
		<tr>
			<th>Gremium:</th>
			<td>
				<?= CHtml::encode($termin->gremium->name) ?>
			</td>
		</tr>
		<?
		zeile_anzeigen($termin->antraegeDokumente, "Dokumente:", function ($dok) {
			/** @var Dokument $dok */
			echo CHtml::encode($dok->getDisplayDate()) . ": " . CHtml::link($dok->getName(false), $dok->getLinkZumDokument());
		});
		?>
		</tbody>
	</table>

	<? if ($to_db) { ?>
		<section id="mapsection">
			<h3>Tagesordnung auf der Karte</h3>

			<div id="mapholder">
				<div id="map"></div>
			</div>
			<!--
			<a href="<?= Yii::app()->createUrl("termine/topGeoExport", array("termin_id" => $termin->id)) ?>" style="float: right;" rel="nofollow">Großversion der Karte exportieren
				<span class="fontello-right-open"></span></a>-->
		</section>
		<br>

		<h3>Tagesordnung</h3>
		<ol style="list-style-type: none;">
			<?
			$geheimer_teil = false;
			$tops          = $termin->tagesordnungspunkteSortiert();
			foreach ($tops as $ergebnis) {
				if ($ergebnis->status == "geheim" && !$geheimer_teil) {
					$geheimer_teil = true;
					echo "</ol><h3>Nicht-Öffentlicher Teil</h3><ol style='list-style-type: none;'>";
				}
				$name = $ergebnis->top_nr . ": " . $ergebnis->getName(true);
				echo "<li style='margin-bottom: 7px;'>";
				if ($ergebnis->top_ueberschrift) echo "<strong>";
				echo CHtml::encode(strip_tags($name));
				if ($ergebnis->top_ueberschrift) echo "</strong>";
				$antraege = $ergebnis->zugeordneteAntraegeHeuristisch();
				if (count($ergebnis->dokumente) > 0 || is_object($ergebnis->antrag) || count($antraege) > 0) {
					echo "<ul class='doks'>";
					$antrag_ids = array();
					if (is_object($ergebnis->antrag)) {
						echo "<li>" . CHtml::link("Sitzungsvorlage", $ergebnis->antrag->getLink()) . "</li>\n";
						$antrag_ids[] = $ergebnis->antrag->id;
					}
					foreach ($ergebnis->dokumente as $dokument) {
						echo "<li>" . CHtml::link($dokument->name, $dokument->getLinkZumDokument());
						$x = explode("Beschluss:", $dokument->text_pdf);
						if (count($x) > 1) echo " (" . CHtml::encode(trim($x[1])) . ")";
						echo "</li>\n";
					}
					foreach ($antraege as $ant) if (is_object($ant)) {
						/** @var Antrag $ant */
						if (in_array($ant->id, $antrag_ids)) continue;
						$antrag_ids[] = $ant->id;
						echo "<li>Verwandter Antrag: " . CHtml::link($ant->getName(true), $ant->getLink()) . "</li>\n";
					} else {
						echo "<li>Verwandter Antrag: " . CHtml::encode(RISTools::korrigiereTitelZeichen($ant)) . "</li>\n";
					}
					echo "</ul>";
				}
				echo "</li>";

				$geo = $ergebnis->get_geo();
				foreach ($geo as $g) $geodata[] = array(
					FloatVal($g->lat),
					FloatVal($g->lon),
					$ergebnis->top_nr . ": " . $ergebnis->getName(true)
				);
			}
			?>
		</ol>
		<script>
			$(function () {
				var geodata = <?=json_encode($geodata)?>;
				if (geodata.length > 0) $(function () {
					var $map = $("#map").AntraegeKarte({
						assetsBase: <?=json_encode($this->getAssetsBase())?>,
						outlineBA: <?=($termin->ba_nr > 0 ? $termin->ba_nr : 0)?>,
						onInit: function () {
							$map.AntraegeKarte("setAntraegeData", geodata, null);
						}
					});
				});
				else $("#mapsection").hide();
			});
		</script>
	<? } elseif ($to_pdf) {
		$this->renderPartial("../index/pdf_embed", array(
			"url" => '/dokumente/' . $to_pdf->id . '.pdf',
		));
	} else {
		echo '<div class="keine_tops">(Noch) Keine Tagesordnung veröffentlicht</div>';
	}
	?>

</section>
