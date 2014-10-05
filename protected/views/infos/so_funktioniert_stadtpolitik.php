<?
/**
 * @var InfosController $this
 */
$this->pageTitle = "So funktioniert Stadtpolitik";

?>

<h2>So funktioniert Stadtpolitik</h2>
<a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>"><span class="glyphicon glyphicon-arrow-left"></span> Zurück</a><br>

<br><br>

<?=CHtml::link("Zum Glossar", array("infos/glossar"))?>
