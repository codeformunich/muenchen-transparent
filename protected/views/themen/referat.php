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
				$this->renderPartial("../index/index_antraege_liste", array(
					"title"             => "Aktuelle Dokumente",
					"antraege"          => $antraege_referat,
					"weiter_links_oben" => false,
				));
				?>
			</section>
		</div>
		<div class="col col-md-5">
			<section class="well">
				<h2>Anschrift</h2>
				<?
				echo CHtml::encode($referat->strasse) . "<br>";
				echo CHtml::encode($referat->plz . " " . $referat->ort) . "<br><br>";
				if ($referat->email != "") echo CHtml::link($referat->email, "mailto:" . $referat->email) . "<br>";
				if ($referat->telefon != "") echo CHtml::encode($referat->telefon) . "<br>";
				if ($referat->website != "") echo '<br><a href="' . CHtml::encode($referat->website) . '" class="btn btn-success">Zur Website <span class="mdi-navigation-chevron-right" style="font-size: 200%; font-weight: bold; float: right; position: absolute; right: 0; top: 5px;"></span></a>';
				?>
				<br><br><br>
				<h2>ReferentIn</h2>
				<?
				foreach ($referat->stadtraetInnenReferate as $str) echo CHtml::encode($str->stadtraetIn->name) . "<br>";
				?>
				<br><br>

			</section>
		</div>
	</div>
<?