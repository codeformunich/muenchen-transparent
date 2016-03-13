<?php
/**
 * @var InfosController $this
 * @var Text $text
 * @var string $my_url
 */

$this->pageTitle = "So funktioniert Stadtpolitik";
$this->load_mediaelement = true;

?>

<section class="well">

    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li class="active">Stadtpolitik</li>
    </ul>


    <div class="row teaser_buttons">
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
                <h2><span class="glyphicon glyphicon-paragraph">§</span> Stadtrecht</h2>

                <div class="description">
                    Satzungen &amp; Verordnungen
                </div>
            </a>
        </div>
    </div>

</section>


<div class="row" id="listen_holder">
    <div class="col col-md-8">
        <section class="start_berichte well fliesstext">
            <?
            if ($this->binContentAdmin()) { ?>
                <a href="#" style="display: inline; float: right;" id="text_edit_caller">
                    <span class="mdi-content-create"></span> Bearbeiten
                </a>
                <a href="#" style="display: none; float: right;" id="text_edit_aborter">
                    <span class="mdi-content-clear"></span> Abbrechen
                </a>
            <? }
            ?>
            <h1>So entsteht Stadtpolitik</h1>

            <br>
            <video width="560" height="306" poster="/media/v1.jpg" controls="controls" preload="none">
                <source type="video/mp4" src="/media/v1.aac.mp4">
                <source type="video/ogg" src="/media/v1.ogv">
                <object width="560" height="306" type="application/x-shockwave-flash" data="/bower/mediaelement/build/flashmediaelement.swf">
                    <param name="movie" value="/bower/mediaelement/build/flashmediaelement.swf" />
                    <param name="flashvars" value="controls=true&file=/media/v1.mp4" />
                    <!-- Image as a last resort -->
                    <img src="/media/v1.jpg" width="560" height="305" title="No video playback capabilities" />
                </object>
            </video>
            <div style="color: gray; text-align: center;">
                Video: Lionel Koch; Skript u. Ton: Bernd Oswald
            </div>
            <br><br>

            <?
            $this->renderPartial("/index/ckeditable_text", array(
                "text"            => $text,
                "my_url"          => $my_url,
                "show_title"      => false,
                "insert_tooltips" => true,
            ))
            ?>

            <script>
                $(function() {
                    $('[data-toggle="tooltip"]').tooltip({animation: true, delay:500});
                    $('video,audio').mediaelementplayer({

                    });
                });
            </script>
        </section>
    </div>


    <div class="col col-md-4">
        <section class="well">
            <h3>Informative Seiten</h3>

            <ul>
                <li><a href="http://www.opengov-muenchen.de/">Das OpenGovernment-Portal der Stadt München</a></li>
                <li><a href="http://codefor.de/muenchen/">OK Lab München</a></li>
                <li><a href="http://www.muenchen.de/rathaus/Stadtpolitik.html">Die Stadtpolitik-Seite der Stadt München</a></li>
                <li>...weitere Links über München und Open Data? <a href="<?=CHtml::encode(Yii::app()->createUrl("infos/feedback"))?>">Schreib uns!</a></li>
            </ul>
        </section>
    </div>
</div>
