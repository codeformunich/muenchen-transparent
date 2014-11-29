<?
/**
 * @var Rechtsdokument $dokument
 * @var InfosController $this
 */



$this->pageTitle = $dokument->name;
$this->inline_css .= $dokument->css;

?>

<div class="row">
    <!--<div class="col col-md-3">
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
    </div>-->
    <div class="col col-md-12">
        <section class="well rechtstext" style="margin-top: 50px;">
            <h1><?=CHtml::encode($dokument->name)?></h1>

            <?=$dokument->html?>


        </section>
    </div>
</div>
