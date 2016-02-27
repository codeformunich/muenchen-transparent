<?php

use yii\helpers\Html;
use Yii;
use app\components\AntiXSS;
use app\models\Dokument;
use app\models\Referat;

/**
 * @var IndexController $this
 */

$this->pageTitle = "Suche";


?>
<section class="well">
	<form class="form-horizontal form-signin" method="POST" action="<?= Html::encode(Yii::$app->createUrl("index/suche")) ?>">
		<fieldset>
			<legend class="form_row">Suche</legend>
		</fieldset>

		<div class="form-group">
			<label for="suche_volltext" class="col-sm-3 control-label">Suchbegriff:</label>

			<div class="col-sm-9">
				<input id="suche_volltext" name="volltext" type="text" class="form-control" placeholder="Volltextsuche">
			</div>
		</div>

		<div class="form-group">
			<label for="suche_nummer" class="col-sm-3 control-label">Antragsnummer:</label>

			<div class="col-sm-9">
				<input id="suche_nummer" name="antrag_nr" type="text" class="form-control" placeholder="z.B. 08-15 / A 38">
			</div>
		</div>

		<div class="form-group">
			<label for="suche_typ" class="col-sm-3 control-label">Typ:</label>

			<div class="col-sm-9">
				<select id="suche_typ" name="typ" size="1" class="form-control">
					<option value="">- egal -</option>
					<? foreach (Dokument::$TYPEN_ALLE as $typ_id => $typ_name) { ?>
						<option value="<?= $typ_id ?>"><?= Html::encode($typ_name) ?></option>
					<? } ?>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="suche_referat" class="col-sm-3 control-label">Zuständiges Referat:</label>

			<div class="col-sm-9">
				<select id="suche_referat" name="referat" size="1" class="form-control">
					<option>- egal -</option>
					<?
					/** @var Referat[] $referate */
					$referate = Referat::findAll();
					foreach ($referate as $ref) {
						?>
						<option value="<?= $ref->id ?>"><?= Html::encode($ref->name) ?></option>
					<? } ?>
				</select>
			</div>
		</div>

		<!-- @TODO: Setzt voraus: offizielles Datum eines Dokuments ermitteln
		<div class="form-group">
			<label for="suche_datum_von" class="col-md-3 control-label">Zeitraum von:</label>

			<div class="col-md-4">
				<input id="suche_datum_von" name="datum_von" type="text" class="form-control">
			</div>

			<label for="suche_datum_bis" class="col-md-1 control-label">Bis:</label>

			<div class="col-md-4">
				<input id="suche_datum_bis" name="datum_bis" type="text" class="form-control">
			</div>
		</div>

		-->

		<div class="" style="text-align: center; margin-top: 10px;">
			<button type="submit" class="btn btn-success" name="<?= AntiXSS::createToken("search_form") ?>"><span class="glyphicon glyphicon-search"></span> Suche</button>
		</div>

		<!-- @TODO: Setzt voraus: offizielles Datum eines Dokuments ermitteln
		<script>
			$(function() {
				$("#suche_datum_von").datepicker();
				$("#suche_datum_bis").datepicker();
			});
		</script>
		-->

	</form>
</section>