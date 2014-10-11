<?php
/**
 * @var Referat $referat
 * @var $antraege_referat
 */

$this->pageTitle = $referat->getName();
?>

	<section class="well">
		<ul class="breadcrumb" style="margin-bottom: 5px;">
			<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
			<li><a href="<?= CHtml::encode(Yii::app()->createUrl("themen/index")) ?>">Themen</a><br></li>
			<li class="active">Referat</li>
		</ul>
		<h1><?= CHtml::encode($referat->getName()) ?></h1>
	</section>

	<div class="row" id="listen_holder">
		<div class="col col-md-7">
			<section class="well">
				<?
				$this->renderPartial("../index/index_antraege_liste2", array(
					"title"             => "Aktuelle Dokumente",
					"antraege"          => $antraege_referat,
					"weiter_links_oben" => false,
				));
				?>
			</section>
		</div>
		<div class="col col-md-5">
			<section class="well">
				<h2>Informationen</h2>
				<h3>Kontakt</h3>

			</section>
		</div>
	</div>
<?