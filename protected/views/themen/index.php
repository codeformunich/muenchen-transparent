<?php
/**
 * @var Dokument[] $highlights
 * @var Referat[] $referate
 * @var Tag[] $tags
 */

$this->pageTitle = "Themen";

?>

	<section class="well">
		<ul class="breadcrumb">
			<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
			<li class="active">Themen</li>
		</ul>
		<h1>Themen</h1>
	</section>

	<div class="row" id="listen_holder">
		<div class="col col-md-6">
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
		<div class="col col-md-6">
			<section class="well">
				<h3>Schlagworte</h3>
				<br>
				<ul>
					<?
					foreach ($tags as $tag) {
						echo '<li>' . $tag->getNameLink() . ' (' . count($tag->antraege) . ')</li>';
					}
					?>

				</ul>
			</section>

			<section class="start_berichte well">
				<a href="<?= CHtml::encode(Yii::app()->createUrl("index/highlights")) ?>" class="weitere">Weitere</a>

				<h3>Berichte / Highlights</h3>
				<ul><?
					foreach ($highlights as $dok) {
						echo "<li>";
						echo CHtml::link($dok->antrag->getName(true), $dok->getLinkZumDokument());
						echo "</li>";
					}
					?>
				</ul>
			</section>
		</div>
	</div>
<?