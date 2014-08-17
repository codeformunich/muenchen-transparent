<?php
/**
 * @var Referat $referat
 * @var $antraege_referat
 */

$this->pageTitle = $referat->getName();
?>
	<h1><?= CHtml::encode($referat->getName()) ?></h1>
	<a href="<?= CHtml::encode(Yii::app()->createUrl("themen/index")) ?>"><span class="glyphicon glyphicon-arrow-left"></span> Zurück</a><br>
	<div class="row" id="listen_holder">
		<div class="col col-lg-5 keine_dokumente">
			<section>
				<?
				$this->renderPartial("../index/index_antraege_liste", array(
					"title"             => "Aktuelle Dokumente",
					"antraege"          => $antraege_referat,
					"weiter_links_oben" => false,
				));
				?>
			</section>
		</div>
		<div class="col col-lg-5 keine_dokumente">
			<section>
				<h3>Ausstehende Anfragen</h3>
				<ul><?

					?>
				</ul>
			</section>
		</div>
		<div class="col col-lg-2 keine_dokumente">
			<section>
				<h3>Kontakt</h3>

			</section>
		</div>
	</div>
<?