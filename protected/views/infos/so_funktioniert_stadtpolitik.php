<?
/**
 * @var InfosController $this
 */
$this->pageTitle = "So funktioniert Stadtpolitik";

?>
<section class="well">
	<ul class="breadcrumb" style="margin-bottom: 5px;">
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
		<li class="active">So funktioniert Stadtpolitik</li>
	</ul>

	<h1>So funktioniert Stadtpolitik</h1>

	<br><br>

	<?= CHtml::link("Zum Glossar", array("infos/glossar")) ?>

</section>
