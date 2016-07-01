<?php
/**
 * @var Dokument[] $highlights
 * @var Referat[] $referate
 * @var Tag[] $tags
 */

$this->pageTitle = "Themen";

?>

    <section class="well">
        <ul class="breadcrumb">
            <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
            <li class="active">Themen</li>
        </ul>
        <h1>Themen</h1>
    </section>

    <div class="row" id="listen_holder">
        <div class="col col-md-8">
            <section class="start_berichte well">
                <h3 id="staedtische_referate">Städtische Referate</h3>
                <ul><?
                    foreach ($referate as $ref) {
                        echo "<li>";
                        echo CHtml::link($ref->getName(true), Yii::app()->createUrl("themen/referat", array("referat_url" => $ref->urlpart)));
                        echo "</li>";
                    }
                    ?>
                </ul>
            </section>
        </div>
        <div class="col col-md-4">
            <section class="well">
                <h3>Schlagworte</h3>
                <div id="list-js-container" class="such-liste">
                    <input class="search" placeholder="Filtern" style="width: 100%;"/>
                    <ul class="list list-unstyled tag-liste">
                        <?
                        usort($tags, function ($a, $b) {
                            if (count($a->antraege) == count($b->antraege))
                                return 0;
                            return (count($a->antraege) > count($b->antraege)) ? -1 : 1;
                        });
                        foreach ($tags as $tag) {
                            echo '<li><span class="list-name">' . $tag->getNameLink() . '<span style="float: right">' . count($tag->antraege) . '<span>' . '</span></li>';
                        }
                        ?>
                    </ul>
                </div>
            </section>
            <!--
            <section class="start_berichte well">
                <a href="<?= CHtml::encode(Yii::app()->createUrl("index/highlights")) ?>" class="weitere">Weitere</a>

                <h3>Berichte / Highlights</h3>
                <ul><?
                    foreach ($highlights as $dok) {
                        echo "<li>";
                        echo CHtml::link($dok->antrag->getName(true), $dok->getLink());
                        echo "</li>";
                    }
                    ?>
                </ul>
            </section>
            -->
        </div>
    </div>

<script src="/bower/list.js/dist/list.min.js"></script>
<script>
var options = {
  valueNames: [ 'list-name' ]
};

var userList = new List("list-js-container", options);
</script>
