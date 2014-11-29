<?
/**
 * @var InfosController $this
 */



$this->pageTitle = "Stadtrecht";

?>

<div class="row">
    <div class="col col-md-3">
        <section class="well" id="auswahl" style="margin-top: 50px;">
            <input class="search" placeholder="Suche" />
            <ul class="list">
            <?
            /** @var Rechtsdokument[] $dokumente */
            $dokumente = Rechtsdokument::model()->findAll();
            foreach ($dokumente as $dok) {
                echo '<li><span class="list-name">' . CHtml::link($dok->name, Yii::app()->createUrl("infos/stadtrechtDokument", array("doknr" => $dok->nr))) . '</span></li>';
            }
            ?>
            </ul>
        </section>
    </div>
    <div class="col col-md-9">
        <section class="well"  style="margin-top: 50px;">
            <ul class="breadcrumb">
                <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
                <li><a href="<?= CHtml::encode(Yii::app()->createUrl("infos/soFunktioniertStadtpolitik")) ?>">Stadtpolitik</a><br></li>
            </ul>
            <h1>Stadtrecht</h1>

            <p style="font-size: 18px;">Hier finden Sie alle Satzungen, Verordnungen und Regelungen vom offizielen
            <a href="http://www.muenchen.info/dir/recht/num_portal.html">Portal für Stadtrecht</a>
            der Stadt München übersichtlich aufbereitet.</p>

        </section>
    </div>
</div>

<script src="/js/list.js"></script>
<script>
var options = {
  valueNames: [ 'list-name' ]
};

var userList = new List('auswahl', options);
</script>
