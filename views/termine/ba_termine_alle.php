<?php

use yii\helpers\Html;
use Yii;

/**
 * @var TermineController $this
 * @var Bezirksausschuss $ba
 * @var array $termine
 */


$this->pageTitle = "Termine des Bezirksausschuss " . $ba->ba_nr . ", " . $ba->name;

?>

<section class="well">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= Html::encode(Yii::$app->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li><a href="<?= Html::encode(Yii::$app->createUrl($ba->getLink())) ?>">BA <?=$ba->ba_nr?></a><br></li>
        <li class="active">Termine</li>
    </ul>
    <h1>Termine des Bezirksausschuss <?= $ba->ba_nr . ", " . Html::encode($ba->name) ?></h1>
    <br>
    <br>
    <?
    $this->renderPartial("termin_liste", array(
        "termine"     => $termine,
        "gremienname" => false,
        "twoCols"     => true,
    ))
    ?>


</section>
