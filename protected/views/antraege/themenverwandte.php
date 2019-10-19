<?php
/**
 * @var Antrag $antrag
 * @var AntraegeController $this
 */

$this->pageTitle = $antrag->getName(true) . " - Themenverwandt";
$related         = $antrag->errateThemenverwandteAntraege(50);

?>

<section class="well themenverwandt_liste">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li><a href="<?= CHtml::encode($antrag->getLink()) ?>"><?= CHtml::encode($antrag->getTypName()) ?></a><br></li>
        <li class="active">Verwandte</li>
    </ul>
    <h1>Möglicherweise verwandte Dokumente</h1>

    <blockquote>
        <p>Die Themenverwandtheit wird automatisiert anhand der Häufung von Wörtern ermittelt und liefert oft interessante Funde - manchmal aber auch Unpassende. Die Ergebnisse auf dieser Seite also mit einer gewissen kritischen Distanz betrachten!</p>
        <footer>Das München-Transparent-Team</footer>
    </blockquote>

    <ul class="list-group">
        <?php
        $this->renderPartial("related_list", array(
            "related" => $related,
            "narrow" => false,
        ));
        ?>
    </ul>
</section>
