<?php
/**
 * @var AntragDokument[] $highlights
 * @var Referat[] $referate
 */

$this->pageTitle = "Themen";

?>

	<section>
		<h1 class="sr-only">Themen</h1>
		<ul class="breadcrumb" style="margin-bottom: 5px;">
			<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
			<li class="active">Themen</li>
		</ul>
	</section>

	<div class="row" id="listen_holder">
		<div class="col col-lg-4 col-md-4">
			<section class="start_berichte well">
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
		<div class="col col-lg-4 col-md-4">
			<section class="well">
				@TODO
			</section>
		</div>
		<div class="col col-lg-4 col-md-4">
			<section class="start_berichte well">
				<a href="<?= CHtml::encode(Yii::app()->createUrl("index/highlights")) ?>" class="weitere">Weitere</a>

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