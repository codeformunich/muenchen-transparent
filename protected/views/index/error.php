<?php
/**
 * @var $this IndexController
 * @var $error array
 * @var string $code
 * @var string $message
 */

$this->pageTitle=Yii::app()->name . ' - Error';
$this->breadcrumbs=array(
    'Error',
);
?>

<h2>Fehler <?php echo $code; ?></h2>

<div class="error">
<? if ($code == 404) { ?>
<p>Die angeforderte Seite existiert nicht.</p>
<? } else { ?>
<?php echo CHtml::encode($message); ?>
<? } ?>
</div>
