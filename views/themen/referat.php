<?php
/**
 * @var Referat $referat
 * @var $antraege_referat
 */

$this->pageTitle = $referat->getName();
?>

<section class="well">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= Html::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li><a href="<?= Html::encode(Yii::app()->createUrl("themen/index")) ?>">Themen</a><br></li>
        <li class="active">Referat</li>
    </ul>
    <h1><?= Html::encode($referat->getName()) ?></h1>

    <?
    $this->renderPartial("/index/ckeditable_text", array(
        "text"            => $text,
        "my_url"          => $my_url,
        "show_title"      => false,
        "insert_tooltips" => false,
    ));
    ?>
</section>

<div class="row" id="listen_holder">
    <div class="col col-md-8">
        <section class="well">
            <?
            $this->renderPartial("../index/index_antraege_liste", array(
                "title"             => "Aktuelle Dokumente",
                "antraege"          => $antraege_referat,
                "weiter_links_oben" => false,
                "zeige_jahr"        => true,
            ));
            ?>
        </section>
    </div>
    <div class="col col-md-4">
        <section class="well">
            <h2>Anschrift</h2>
            <?
            echo Html::encode($referat->strasse) . "<br>";
            echo Html::encode($referat->plz . " " . $referat->ort) . "<br>";
            if ($referat->telefon != "") echo "Tel.: " . Html::encode($referat->telefon) . "<br>";
            echo "<br>";
            if ($referat->email   != "") echo '<a href=mailto:' . Html::encode($referat->email  ) . '  class="btn btn-primary" style="width: 150px">E-Mail  <span class="mdi-navigation-chevron-right" style="font-size: 200%; font-weight: bold; float: right; position: absolute; right: 0; top: 5px;"></span></a>';
            if ($referat->website != "") echo '<a href="'       . Html::encode($referat->website) . '" class="btn btn-primary" style="width: 150px">Website <span class="mdi-navigation-chevron-right" style="font-size: 200%; font-weight: bold; float: right; position: absolute; right: 0; top: 5px;"></span></a>';
            ?>
            <br><br>
            <h2>ReferentIn</h2>
            <?
            foreach ($referat->stadtraetInnenReferate as $str) echo Html::encode($str->stadtraetIn->name) . "<br>";
            ?>
            <br><br>

        </section>
    </div>
</div>
<?
