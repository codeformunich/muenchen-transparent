<?
/**
 * @var InfosController $this
 */
$this->pageTitle = "Personen";

/**
 * @var array[] $fraktionen
 */
?>

<section class="well">
	<ul class="breadcrumb" style="margin-bottom: 5px;">
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
		<li class="active">Personen</li>
	</ul>
	<h1>Personen</h1>
</section>

<div class="row" id="listen_holder">
	<div class="col col-lg-6 col-md-6"><?
		$this->renderPartial("fraktionen", array(
			"fraktionen" => $fraktionen,
			"title"      => "StadträtInnen",
		));?>

		<section class="well">
			<h2>Städtische Referate</h2>
			...
		</section>
	</div>
	<div class="col col-lg-6 col-md-6">
		<section class="well">
			<h2>Stadtteilpolitik</h2>
			...
		</section>
	</div>
</div>
