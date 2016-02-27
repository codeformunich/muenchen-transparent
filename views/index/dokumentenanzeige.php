<?php

use yii\helpers\Html;
use Yii;
use app\models\Antrag;
use app\models\Tagesordnungspunkt;
use app\models\Termin;
use app\models\Vorgang;

?>

﻿<?
/**
 * @var int $id
 * @var IndexController $this
 * @var Dokument|null $dokument
 */

$risitem = $dokument->getRISItem();
if ($risitem) {
    $this->pageTitle = $risitem->getName(true) . ": " . $dokument->getName();
} else {
    $this->pageTitle = $dokument->getName();
}
?>


<section class="well pdfjs">
    <ul class="breadcrumb">
        <li><a href="<?= Html::encode(Yii::$app->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <?

        /**
         * @param IRISItemHasDocuments $uebergruppe
         * @param string $name
         * @param Dokument $dokument
         * @param string $link
         */
        function dokumentenliste($uebergruppe, $name, $dokument, $link)
        {
            if ($link && $uebergruppe) {
                echo "<li>" . Html::link($name, $uebergruppe->getLink()) . "<br></li>";
            } else {
                echo "<li class=\"active\">" . Html::encode($name) . "<br></li>";
            }

            if ($uebergruppe && count($uebergruppe->getDokumente()) > 1) {
                ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= Html::encode($dokument->getName()) ?><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <? foreach ($uebergruppe->getDokumente() as $dok) echo "<li>" . Html::link($dok->getName(), $dok->getLinkZumDokument()) . "</li>\n" ?>
                    </ul>
                </li> <?
            } else {
                echo "<li>" . Html::link($dokument->getName(), $dokument->getLinkZumDokument()) . "</li>\n";
            }
        }

        if ($dokument != null) {
            if ($dokument->antrag_id) dokumentenliste(Antrag::findOne($dokument->antrag_id), "Antragsseite", $dokument, true);
            else if ($dokument->tagesordnungspunkt_id) dokumentenliste(Tagesordnungspunkt::findOne($dokument->tagesordnungspunkt_id), "Ergebnisseite", $dokument, true);
            else if ($dokument->termin_id) dokumentenliste(Termin::findOne($dokument->termin_id), "Terminseite", $dokument, true);
            else if ($dokument->vorgang_id) dokumentenliste(Vorgang::findOne($dokument->vorgang_id), "Vorgangsseite", $dokument, false);
            else     echo "<li class=\"active\">" . Html::encode($dokument->getName()) . "</li>";
        }
        ?>
    </ul>
    <div class="pdf_download_holder"><a href="<?= Html::encode($dokument->getLink()) ?>" download="<?= $dokument->antrag_id ?> - <?= Html::encode($dokument->getName()) ?>"><span class="glyphicon glyphicon-print"></span> Druckansicht</a></div>

    <?
    $this->renderPartial("pdf_embed", array(
        "url" => '/dokumente/' . $id . '.pdf',
    ));
    ?>

    <div id="pdf_rechtsvermerk">
        Originaldokument von <a href="http://www.ris-muenchen.de/">www.ris-muenchen.de</a>. München Transparent ist nicht für den Inhalt dieses Dokuments verantwortlich.
    </div>

    <script>
        // Fix the problem that pdf js doesn't get the height automatically (maybe because of the footer)
        function pdf_resize() {
            var $container = $("#mainContainer"),
                border = 95;
            if (!$("#pdf_rechtsvermerk").is(":visible")) border -= 10;
            var container_height = $(window).height() - $("body > footer").height() - $("#main_navbar").height() - border;
            $container.height(container_height);
            $container.parents(".well").height(container_height + 22);
        }

        $(pdf_resize);
        $(window).resize(pdf_resize);
    </script>
</section>
