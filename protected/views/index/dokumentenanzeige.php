<?php
/**
 * @var int $id
 * @var IndexController $this
 * @var Dokument $dokument
 */

if ($dokument->getRISItem()) {
    $this->pageTitle = $dokument->getRISItem()->getName(true) . ": " . $dokument->getName();
} else {
    $this->pageTitle = $dokument->getName();
}
?>


<section class="well pdfjs">
    <ul class="breadcrumb">
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl('index/startseite')) ?>">Startseite</a><br></li>
        <?php
        $uebergruppe = null;
        $uebergruppe_title = null;
        if ($dokument->antrag_id) {
            $uebergruppe = Antrag::model()->findByPk($dokument->antrag_id);
            $uebergruppe_title = 'Antragsseite';
        } else if ($dokument->tagesordnungspunkt_id) {
            $uebergruppe = Tagesordnungspunkt::model()->findByPk($dokument->tagesordnungspunkt_id);
            $uebergruppe_title = 'Ergebnisseite';
        } else if ($dokument->termin_id) {
            $uebergruppe = Termin::model()->findByPk($dokument->termin_id);
            $uebergruppe_title = 'Terminseite';
        }

        if ($uebergruppe) {
            echo "<li>" . CHtml::link($uebergruppe_title, $uebergruppe->getLink()) . "<br></li>";
        }

        $weitere = $uebergruppe && count($uebergruppe->getDokumente()) > 1;
        ?>

        <li class="active"><?= CHtml::encode($dokument->getName()) ?></li>

        <?php // Rechter Bereich ?>

        <li class="pdf-download-button <?php if(!$weitere) echo 'kein-slash-davor' ?>"><a href="<?= CHtml::encode($dokument->getLinkZumOrginal()) ?>" download="<?= $dokument->antrag_id ?> - <?= CHtml::encode($dokument->getName()) ?>"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Druckansicht</a></li>
        <?php if ($weitere) { ?>
            <li class="dropdown weitere-dokumente kein-slash-davor">

                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-file"></span> Weitere Dokumente<span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <?php foreach ($uebergruppe->getDokumente() as $dok) echo "<li>" . CHtml::link($dok->getName(), $dok->getLink()) . "</li>\n" ?>
                </ul>
            </li>
        <?php } ?>
    </ul>

    <?php $this->load_pdf_js = true; ?>
    <?php
    $this->renderPartial("pdf_embed", [
        "url" => $dokument->getDownloadLink(),
    ]);
    ?>

    <div id="pdf_rechtsvermerk">
        Originaldokument von <a href="<?= RIS_URL_PREFIX ?>">ris-muenchen.de</a>. München Transparent ist nicht für den Inhalt dieses Dokuments verantwortlich.
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
