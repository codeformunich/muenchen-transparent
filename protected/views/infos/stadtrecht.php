<?php
/**
 * @var InfosController $this
 */



$this->pageTitle = "Stadtrecht";

?>

<section class="well" id="rechtsdokumente">
    <ul class="breadcrumb">
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("infos/soFunktioniertStadtpolitik")) ?>">Stadtpolitik</a><br></li>
    </ul>
    <h1>Stadtrecht</h1>

    <p style="font-size: 18px;">Hier finden Sie alle Satzungen, Verordnungen und Regelungen vom offiziellen
        <a href="http://www.muenchen.info/dir/recht/num_portal.html">Portal für Stadtrecht</a>
        der Stadt München übersichtlich aufbereitet.</p>
    <div class="such-liste">
        <input class="search" placeholder="Filtern"/>
        <ul class="list list-unstyled">
        <?
        /** @var Rechtsdokument[] $dokumente */
        $dokumente = Rechtsdokument::model()->alle_sortiert();
        foreach ($dokumente as $dok) {
            echo '<li><span class="list-name">' . CHtml::link($dok->titel_lang(), Yii::app()->createUrl("infos/stadtrechtDokument", array("id" => $dok->id))) . '<span style="display: none;">' . CHtml::encode($dok->titel) . '</span></span></li>' . "\n";
        }
        ?>
        </ul>
    </div>

</section>

<? $this->load_list_js = true; ?>
<script>
var userList = new List('rechtsdokumente', { valueNames: ['list-name']});
</script>
