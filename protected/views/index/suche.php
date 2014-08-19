<?php
/**
 * @var IndexController $this
 */

$this->pageTitle = "Suche";


?>
<h1>Suche</h1>

<form method="POST" action="<?= CHtml::encode(Yii::app()->createUrl("index/suche")) ?>" class="form-docsearch" style="">
	<div class="form-group">
		<label for="suche_volltext" class="col-sm-3 control-label">Suchbegriff:</label>

		<div class="col-sm-9">
			<input id="suche_volltext" name="volltext" type="text" class="form-control" placeholder="Volltext">
		</div>
	</div>
	<br style="clear: both;">

	<div class="form-group">
		<label for="suche_nummer" class="col-sm-3 control-label">Antragsnummer:</label>

		<div class="col-sm-9">
			<input id="suche_nummer" name="antrag_nr" type="text" class="form-control" placeholder="z.B. 08-15 / A 38">
		</div>
	</div>
	<br style="clear: both;">

	<div class="form-group">
		<label for="suche_typ" class="col-sm-3 control-label">Typ:</label>

		<div class="col-sm-9">
			<select id="suche_typ" name="typ" size="1" class="form-control">
				<option value="">- egal -</option>
				<? foreach (AntragDokument::$TYPEN_ALLE as $typ_id => $typ_name) { ?>
					<option value="<?= $typ_id ?>"><?= CHtml::encode($typ_name) ?></option>
				<? } ?>
			</select>
		</div>
	</div>
	<br style="clear: both;">

	<div class="form-group">
		<label for="suche_referat" class="col-sm-3 control-label">Zuständiges Referat:</label>

		<div class="col-sm-9">
			<select id="suche_referat" name="type" size="1" class="form-control">
				<option>- egal -</option>
				<?
				/** @var Referat[] $referate */
				$referate = Referat::model()->findAll();
				foreach ($referate as $ref) { ?>
					<option value="<?= $ref->id ?>"><?= CHtml::encode($ref->name) ?></option>
				<? } ?>
			</select>
		</div>
	</div>
	<br style="clear: both;">

	<div class="form-group">
		<label for="suche_datum_von" class="col-sm-3 control-label">Zeitraum von:</label>

		<div class="col-sm-4">
			<input id="suche_datum_von" name="datum_von" type="text" class="form-control">
		</div>

		<label for="suche_datum_bis" class="col-sm-1 control-label">Bis:</label>

		<div class="col-sm-4">
			<input id="suche_datum_bis" name="datum_bis" type="text" class="form-control">
		</div>
	</div>
	<br style="clear: both;">
	<div class="" style="text-align: center; margin-top: 10px;">
		<button type="submit" class="btn btn-success" name="<?=AntiXSS::createToken("search_form")?>"><span class="glyphicon glyphicon-search"></span> Suche</button>
	</div>

</form>