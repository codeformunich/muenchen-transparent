<?php

use yii\helpers\Html;
use Yii;
use app\components\AntiXSS;

/**
 * @var InfosController $this
 * @var Text[] $eintraege
 */
$this->pageTitle = "Glossar";

?>
<section class="well">
	<ul class="breadcrumb" style="margin-bottom: 5px;">
		<li><a href="<?= Html::encode(Yii::$app->createUrl("index/startseite")) ?>">Startseite</a><br></li>
		<li><a href="<?= Html::encode(Yii::$app->createUrl("infos/soFunktioniertStadtpolitik")) ?>">So funktioniert Stadtpolitik</a><br></li>
		<li class="active">Glossar</li>
	</ul>

	<h1>Glossar</h1>

	<br>
	<br>

	<dl class="glossar dl-horizontal" style="max-width: 850px; margin-left: auto; margin-right: auto;">
		<?
		foreach ($eintraege as $eintrag) {
			echo '<dt id="eintrag_' . str_replace(' ', '-', $eintrag->titel) . '">';
			if ($this->binContentAdmin()) echo ' <a href="' . Html::encode($this->createUrl("infos/glossarBearbeiten", array("id" => $eintrag->id))) . '" title="Bearbeiten"><span class="glyphicon glyphicon-pencil"></span></a>';
			echo Html::encode($eintrag->titel);
			echo '</dt>';
			echo '<dd>' . $eintrag->text . '</dd>';
		}
		?>
	</dl>
	<?

	if ($this->binContentAdmin()) {
		?>
		<div style="text-align: center;"><a href="#" id="glossar_anlegen_caller">
				<span class="glyphicon glyphicon-plus"></span> Neuen Eintrag anlegen
			</a></div>
		<form method="POST" action="<?= Html::encode(Yii::$app->createUrl("infos/glossar")) ?>" role="form" class="well"
			  style="max-width: 850px; margin-top: 50px; margin-left: auto; margin-right: auto; display: none;" id="glossar_anlegen_form">
			<h3>Neuen Eintrag anlegen</h3>

			<div class="form-group">
				<label for="glossary_new_title">Begriff</label>
				<input type="text" name="titel" class="form-control" id="glossary_new_title" placeholder="Zu erklärender Begriff" required>
			</div>

			<div class="form-group">
				<label for="glossary_new_text">Erkärung</label>

				<textarea id="glossary_new_text" name="text" cols="80" rows="10"></textarea>
			</div>

			<div style="text-align: center;">
				<button type="submit" class="btn btn-primary" name="<?= AntiXSS::createToken("anlegen") ?>">Anlegen</button>
			</div>
		</form>

		<script src="/bower/ckeditor/ckeditor.js"></script>
		<script>
			$(function () {
				$("#glossar_anlegen_caller").click(function (ev) {
					ev.preventDefault();
					$(this).hide();
					$("#glossar_anlegen_form").show();

					ckeditor_init($("#glossary_new_text"));
					$("#glossary_new_title").focus();
				});
			});
		</script>
	<?
	}
	?>
	<script>
		$(function() {
			if (location.hash) {
				var x = location.hash.split("#");
				if ($("#eintrag_" + x[1]).length > 0) $("#eintrag_" + x[1]).scrollintoview({top_offset: -250});
			}
		});
	</script>
</section>
