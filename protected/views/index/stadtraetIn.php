<?php
/**
 * @var StadtraetIn $stadtraetIn
 * @var IndexController $this
 */

$this->pageTitle = $stadtraetIn->getName();


?>
<section class="well">
	<ul class="breadcrumb" style="margin-bottom: 5px;">
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/personen")) ?>">Personen</a><br></li>
		<li class="active"><?= CHtml::encode($stadtraetIn->getName()) ?></li>
	</ul>

	<div style="float: right;"><?
		echo CHtml::link("<span class='fontello-right-open'></span> Original-Seite im RIS", $stadtraetIn->getSourceLink());
		?></div>
	<h1><?= CHtml::encode($stadtraetIn->getName()) ?></h1>
</section>

<div class="row">
	<div class="col-md-8">
		<section class="well">
			<table class="table">
				<tbody>
				<tr>
					<th>Fraktion(en):</th>
					<td>
						<ul>
							<? foreach ($stadtraetIn->stadtraetInnenFraktionen as $frakts) {
								echo "<li>" . CHtml::encode($frakts->fraktion->name);
								if ($frakts->fraktion->ba_nr > 0) {
									echo ", Bezirksausschuss " . $frakts->fraktion->ba_nr . " (" . CHtml::encode($frakts->fraktion->bezirksausschuss->name) . ")";
									// @Wird noch nicht zuverlässig erkannt; siehe https://github.com/codeformunich/Ratsinformant/issues/38
								} elseif ($frakts->datum_von > 0 && $frakts->datum_bis > 0) {
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
				<? if (count($stadtraetIn->antraege) > 0) { ?>
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
				<? } ?>
				</tbody>
			</table>
		</section>
	</div>
	<section class="col-md-4">
		<div class="well">
			<h2>Weitere Infos</h2>
			@TODO
		</div>
	</section>
</div>