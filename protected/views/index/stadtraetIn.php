<?php
/**
 * @var StadtraetIn $stadtraetIn
 * @var IndexController $this
 */

$this->pageTitle = $stadtraetIn->getName();


?>
<h1><?= CHtml::encode($stadtraetIn->getName()) ?></h1>

<div class="row">
	<div class="col-md-8">
		<table class="table table-bordered">
			<tbody>
			<tr>
				<th>Fraktion(en):</th>
				<td>
					<div style="float: right;"><?
						echo CHtml::link("<span class='icon-right-open'></span> Original-Seite im RIS", $stadtraetIn->getSourceLink());
						?></div>
					<ul>
						<? foreach ($stadtraetIn->stadtraetInnenFraktionen as $frakts) {
							echo "<li>" . CHtml::encode($frakts->fraktion->name);
							if ($frakts->datum_von > 0 && $frakts->datum_bis > 0) {
								echo " (von " . RISTools::datumstring($frakts->datum_von);
								echo " bis " . RISTools::datumstring($frakts->datum_bis) . ")";
							} elseif ($frakts->datum_von > 0) {
								echo " (seit " . RISTools::datumstring($frakts->datum_von) . ")";
							}
							echo "</li>";
						} ?>
					</ul>
				</td>
			</tr>
			<tr>
				<th>Anträge:</th>
				<td>
					<ul>
						<?
						foreach ($stadtraetIn->antraege as $antrag) {
							echo "<li>";
							echo CHtml::link($antrag->getName(true), $antrag->getLink());
							echo " (" . RISTools::datumstring($antrag->gestellt_am) . ")";
							echo "</li>\n";
						}
						?>
					</ul>
				</td>
			</tr>

			</tbody>
		</table>
	</div>
	<section style="background-color: #f7f7f7; padding-top: 10px; padding-bottom: 10px;" class="col-md-4">
	</section>
</div>