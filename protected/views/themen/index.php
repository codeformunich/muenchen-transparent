<?php
/**
 * @var AntragDokument[] $highlights
 * @var Referat[] $referate
 */

$this->pageTitle = "Themen";

?>
	<h1>Themen</h1>
	<a href="<?= CHtml::encode(Yii::app()->createUrl("index/stadtrat")) ?>"><span class="glyphicon glyphicon-arrow-left"></span> Zurück</a><br>

	<div class="row" id="listen_holder">
		<div class="col col-lg-4 keine_dokumente">
			<section class="start_berichte">
				<h3>Städtische Referate</h3>
				<ul><?
					foreach ($referate as $ref) {
						echo "<li>";
						echo CHtml::link($ref->getName(true), Yii::app()->createUrl("themen/referat", array("referat_url" => $ref->urlpart)));
						echo "</li>";
					}
					?>
				</ul>
			</section>
		</div>
		<div class="col col-lg-4 keine_dokumente">

		</div>
		<div class="col col-lg-4 keine_dokumente">
			<section class="start_berichte">
				<a href="<?=CHtml::encode(Yii::app()->createUrl("index/highlights"))?>" class="weitere">Weitere</a>
				<h3>Berichte / Highlights</h3>
				<ul><?
					foreach ($highlights as $dok) {
						echo "<li>";
						echo CHtml::link($dok->antrag->getName(true), $dok->getOriginalLink());
						echo "</li>";
					}
					?>
				</ul>
			</section>
		</div>
	</div>
<?