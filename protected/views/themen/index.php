<?php
/**
 * @var AntragDokument[] $highlights
 */

$this->pageTitle = "Themen";

?>
	<h1>Themen</h1>


	<div class="row" id="listen_holder">
		<div class="col col-lg-4 keine_dokumente">

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