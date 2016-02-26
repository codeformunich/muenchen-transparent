<?php

use yii\helpers\Html;
use Yii;
use app\components\AntiXSS;
use yii\helpers\Url;

/**
 * @var InfosController $this
 * @var Text $eintrag
 */
$this->pageTitle = "Glossar bearbeiten";

?>
	<h2>Eintrag bearbeiten</h2>
	<a href="<?= Html::encode(Yii::$app->createUrl("infos/glossar")) ?>"><span class="glyphicon glyphicon-arrow-left"></span> Zurück</a><br>

	<form method="POST" action="<?= Html::encode(Yii::$app->createUrl("infos/glossarBearbeiten", ["id" => $eintrag->id])) ?>" role="form" class="well"
		  style="max-width: 850px; margin-top: 50px; margin-left: auto; margin-right: auto;">

		<div class="form-group">
			<label for="glossary_new_title">Begriff</label>
			<input type="text" name="titel" class="form-control" id="glossary_new_title" placeholder="Zu erklärender Begriff" value="<?=Html::encode($eintrag->titel)?>" required>
		</div>

		<div class="form-group">
			<label for="glossary_new_text">Erkärung</label>

			<textarea id="glossary_new_text" name="text" cols="80" rows="10"><?=Html::encode($eintrag->text)?></textarea>
		</div>

		<a href="<?=Html::encode(Url::to("infos/glossarBearbeiten", ["id" => $eintrag->id, AntiXSS::createToken("del") => "1"]))?>" id="eintrag_del_caller" style="color: red; float: right;">
			<span class="glyphicon glyphicon-minus"></span> Eintrag löschen
		</a>

		<div style="text-align: center;">
			<button type="submit" class="btn btn-primary" name="<?= AntiXSS::createToken("speichern") ?>">Speichern</button>
		</div>
	</form>

	<script src="/bower/ckeditor/ckeditor.js"></script>
	<script>
		$(function () {
			$("#eintrag_del_caller").click(function(ev) {
				if (!confirm("Diesen Eintrag wirklich löschen?")) ev.preventDefault();
			});
			ckeditor_init($("#glossary_new_text"));
		});
	</script>
<?
