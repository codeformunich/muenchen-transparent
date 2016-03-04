<?php

use yii\helpers\Html;
use Yii;
use app\models\Rechtsdokument;

/**
 * @var InfosController $this
 */



$this->title = "Stadtrecht";

?>

<section class="well" id="rechtsdokumente">
    <ul class="breadcrumb">
        <li><a href="<?= Html::encode(Url::to("index/startseite")) ?>">Startseite</a><br></li>
        <li><a href="<?= Html::encode(Url::to("infos/soFunktioniertStadtpolitik")) ?>">Stadtpolitik</a><br></li>
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
        $dokumente = Rechtsdokument::find()->alle_sortiert();
        foreach ($dokumente as $dok) {
            echo '<li><span class="list-name">' . Html::a($dok->titel_lang(), Url::to("infos/stadtrechtDokument", array("id" => $dok->id))) . '<span style="display: none;">' . Html::encode($dok->titel) . '</span></span></li>' . "\n";
        }
        ?>
        </ul>
    </div>

</section>

<script src="/bower/list.js/dist/list.min.js"></script>
<script>
var options = {
  valueNames: [ 'list-name' ]
};

var userList = new List('rechtsdokumente', options);
</script>
