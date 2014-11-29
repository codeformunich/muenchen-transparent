<?
/**
 * @var InfosController $this
 */



$this->pageTitle = "Stadtrecht";

?>

<div class="row">
    <div class="col col-md-3">
        <section class="well" style="margin-top: 50px;">
            <ul>
            <?
            /** @var Rechtsdokument[] $dokumente */
            $dokumente = Rechtsdokument::model()->findAll();
            foreach ($dokumente as $dok) {
                echo '<li>' . CHtml::link($dok->name, Yii::app()->createUrl("infos/stadtrechtDokument", array("doknr" => $dok->nr))) . '</li>';
            }
            ?>
            </ul>
        </section>
    </div>
    <div class="col col-md-9">
        <section class="well"  style="margin-top: 50px;">
            <h1>Testsatzung</h1>


            Hier finden Sie....

            <a href="http://www.muenchen.info/dir/recht/num_portal.html">Originalseite</a>

        </section>
    </div>
</div>
