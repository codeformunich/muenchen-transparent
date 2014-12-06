<?
/**
 * @var Rechtsdokument $dokument
 * @var InfosController $this
 */



$this->pageTitle = $dokument->titel;
$this->inline_css .= $dokument->css;

?>
<section class="well">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("infos/stadtrecht")) ?>">Stadtrecht</a><br></li>
        <li class="active"><?= $dokument->titel ?></li>
    </ul>

    <h1><?= CHtml::encode($dokument->titel)?> <span style="float: right"><a href="<?= $dokument->url_pdf ?>"</a>als pdf</span></h1>
</section>


<div class="row">
    <!--<div class="col col-md-3">
        <section class="well" style="margin-top: 50px;">
            <ul>
                <?
                /** @var Rechtsdokument[] $dokumente */
                $dokumente = Rechtsdokument::model()->findAll();
                foreach ($dokumente as $dok) {
                    echo '<li>' . CHtml::link($dok->titel, Yii::app()->createUrl("infos/stadtrechtDokument", array("id" => $dok->id))) . '</li>';
                }
                ?>
            </ul>
        </section>
    </div>-->
    <div class="col col-md-12">
        <section class="well rechtstext" style="margin-top: 50px;">
            <?= $dokument->html ?>
        </section>
    </div>
</div>
