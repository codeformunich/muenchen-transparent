<?
/**
 * @var InfosController $this
 */

$this->pageTitle = "So funktioniert Stadtpolitik";

?>

<section class="well">

    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li class="active">Stadtpolitik</li>
    </ul>

    <h1>Über Stadtpolitik</h1>

</section>

<div class="well">
    <section class="row teaser_buttons">
        <div class="col col-md-6">
            <a href="<?= CHtml::encode(Yii::app()->createUrl("infos/glossar")) ?>"
               class="btn btn-success">
                <h2><span class="glyphicon glyphicon-info-sign"></span>Glossar</h2>

                <div class="description">
                    Wichtige Begriffe erklärt
                </div>
            </a>
        </div>

        <div class="col col-md-6">
            <a href="<?= CHtml::encode(Yii::app()->createUrl("infos/stadtrecht")) ?>"
               class="btn btn-success">
                <h2><span class="glyphicon paragraph">§</span> Stadtrecht</h2>

                <div class="description">
                    Satzungen &amp; Verordnungen
                </div>
            </a>
        </div>
    </section>

</div>


<div class="row" id="listen_holder">
    <div class="col col-md-8">
        <section class="start_berichte well">
            <h3>So entsteht Stadtpolitik</h3>
        </section>
    </div>


    <div class="col col-md-4">
        <section class="well">
            <h3>Informative Seiten</h3>

        </section>
    </div>
</div>
