<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var Antrag $antrag
 * @var AntraegeController $this
 */

$this->title = $antrag->getName(true) . " - Themenverwandt";
$related         = $antrag->errateThemenverwandteAntraege(50);

?>

<section class="well themenverwandt_liste">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= Html::encode(Url::to("index/startseite")) ?>">Startseite</a><br></li>
        <li><a href="<?= Html::encode($antrag->getLink()) ?>"><?= Html::encode($antrag->getTypName()) ?></a><br></li>
        <li class="active">Verwandte</li>
    </ul>
    <h1>Möglicherweise verwandte Dokumente</h1>

    <blockquote>
        <p>Die Themenverwandtheit wird automatisiert anhand der Häufung von Wörtern ermittelt und liefert oft interessante Funde - manchmal aber auch Unpassende. Die Ergebnisse auf dieser Seite also mit einer gewissen kritischen Distanz betrachten!</p>
        <footer>Das München-Transparent-Team</footer>
    </blockquote>

    <ul class="list-group">
        <?
        echo $this->render("related_list", array(
            "related" => $related,
            "narrow" => false,
        ));
        ?>
    </ul>
</section>