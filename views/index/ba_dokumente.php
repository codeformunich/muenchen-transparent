<?php

/**
 * @var IndexController $this
 * @var Bezirksausschuss $ba
 * @var Antrag[] $antraege
 * @var string|null $aeltere_url_ajax
 * @var string|null $aeltere_url_std
 * @var string|null $neuere_url_ajax
 * @var string|null $neuere_url_std
 * @var bool $explizites_datum
 * @var array $geodata
 * @var array $geodata_overflow
 * @var string $datum_von
 * @var string $datum_bis
 * @var int $tage_vergangenheit_dokumente
 */

$this->pageTitle = "Bezirksausschuss " . $ba->ba_nr . ", " . $ba->name;

?>


<section class="well">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= Html::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li><a href="<?= Html::encode(Yii::app()->createUrl($ba->getLink())) ?>">BA <?=$ba->ba_nr?></a><br></li>
        <li class="active">Dokumente</li>
    </ul>

    <h1><?= Html::encode($ba->name) ?>
        <small>(Bezirksausschuss <?= $ba->ba_nr ?>)</small>
    </h1>


    <div class="nur_dokumente two_cols" id="listen_holder">
        <div id="stadtratsdokumente_holder">
            <? $this->renderPartial("index_antraege_liste", array(
                "aeltere_url_std"   => $aeltere_url_std,
                "neuere_url_std"    => $neuere_url_std,
                "antraege"          => $antraege,
                "datum_von"         => $datum_von,
                "datum_bis"         => $datum_bis,
                "title"             => ($explizites_datum ? null : "Dokumente der letzten $tage_vergangenheit_dokumente Tage"),
                "weitere_url"       => null,
                "weiter_links_oben" => true,
                "zeige_ba_orte"     => $ba->ba_nr,
            )); ?>
        </div>
    </div>
</section>
