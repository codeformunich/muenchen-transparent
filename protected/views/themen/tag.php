<?php
/**
 * @var Tag
 * @var $antraege_tag
 */
$this->pageTitle = $tag->name;
?>

	<section class="well two_cols">
		<ul class="breadcrumb" style="margin-bottom: 5px;">
			<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
			<li><a href="<?= CHtml::encode(Yii::app()->createUrl("themen/index")) ?>">Themen</a><br></li>
			<li class="active">Schlagwort</li>
		</ul>
		<h1>Anträge und Vorlagen mit dem Schlagwort "<?= CHtml::encode($tag->name) ?>"</h1>
		<?
		$this->renderPartial("../index/index_antraege_liste", array(
			"title"             => "",
			"antraege"          => $antraege_tag,
			"weiter_links_oben" => false,
            "zeige_jahr"        => true,
		));
		?>
	</section>
<?
