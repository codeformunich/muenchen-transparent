<?php

use yii\helpers\Html;
use Yii;
use app\models\BenutzerIn;
use app\models\Bezirksausschuss;
use yii\helpers\Url;

/**
 * @var RISBaseController $this
 * @var string $content
 */
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?
    if ($this->context->html_description != "") echo Html::encode($this->context->html_description);
    else echo "Münchens Stadtpolitik einfach erklärt. Aktuelle Entscheidungen und Dokumente im alternativen Ratsinformationssystem.";
    ?>">
    <meta name="author" content="Tobias Hößl, Konstantin Schütze">

    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png?1">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png?1">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png?1">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png?1">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png?1">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png?1">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png?1">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png?1">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png?1">
    <link rel="icon" type="image/png" href="/favicon-96x96.png?1" sizes="96x96">
    <link rel="icon" type="image/png" href="/favicon-16x16.png?1" sizes="16x16">
    <link rel="icon" type="image/png" href="/favicon-32x32.png?1" sizes="32x32">
    <meta name="msapplication-TileColor" content="#0f9d58">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png?1">
    <meta property="og:image" content="/images/fb-img2.png">

    <link rel="search" type="application/opensearchdescription+xml" title="<?= Html::encode(Yii::$app->params['projectTitle']) ?>" href="/OpenSearch.xml">
    <link rel="icon" type="image/png" href="/favicon-96x96.png?1">

    <title><?php
        echo Html::encode($this->title);
        if (strpos($this->title, "Transparent") === false) echo " (" . Html::encode(Yii::$app->params['projectTitle']) . ")";
        ?></title>


    <link rel="stylesheet" href="/css/build/website.css">

    <?
    if ($this->context->load_mediaelement)     echo '<link rel="stylesheet" href="/bower/mediaelement/build/mediaelementplayer.min.css">';
    if ($this->context->load_leaflet_draw_css) echo '<link rel="stylesheet" href="/bower/leaflet.draw/dist/leaflet.draw.css">';
    if ($this->context->load_calendar)         echo '<link rel="stylesheet" href="/bower/fullcalendar/dist/fullcalendar.min.css">';
    if ($this->context->load_selectize_js)     echo '<link rel="stylesheet" href="/css/selectizejs.ratsinformant.css">';

    if ($this->context->load_pdf_js) { ?>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="google" content="notranslate">
        <link rel="stylesheet" href="/pdfjs/viewer.css"/>
    <? } ?>

    <? if ($this->context->inline_css != "") {
        echo '<style>' . $this->context->inline_css . '</style>';
    } ?>

    <script src="/bower/jquery/dist/jquery.min.js"></script>

    <? if ($this->context->load_pdf_js) { ?>
        <link rel="resource" type="application/l10n" href="/pdfjs/locale/locale.properties"/>
        <script src="/pdfjs/viewer.min.js" defer></script>
    <? }
    if ($this->context->load_mediaelement) echo '<script src="/bower/mediaelement/build/mediaelement-and-player.min.js" defer></script>';
    if ($this->context->load_selectize_js) echo '<script src="/js/selectize.js-0.11.2/dist/js/standalone/selectize.min.js" defer></script>';
    if ($this->context->load_shariff) echo '<script src="/bower/shariff/build/shariff.min.js" defer></script>';
    ?>
    <script src="/js/build/std.js" defer></script>
</head>

<body>

<script src="/js/modernizr.js"></script>
<? echo ris_intern_html_extra_headers(); ?>

<a href="#page_main_content" class="sr-only">Zum Seiteninhalt</a>

<div class="over_footer_wrapper">
    <div class="clear"></div>

    <div class="navbar navbar-inverse navbar-fixed-top" id="main_navbar">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="sr-only">Menü</span>
                </button>
            </div>
            <div class="navbar-collapse collapse">
                <form class="navbar-form navbar-right" method="POST" action="<?= Html::encode(Url::to("index/suche")) ?>" id="quicksearch_form">
                    <label for="quicksearch_form_input" style="display: none;">Volltextsuche - Suchbegriff:</label>
                    <input type="text" name="suchbegriff" value="<?= Html::encode($this->context->suche_pre) ?>" placeholder="Volltextsuche" class="form-control"
                           id="quicksearch_form_input" required
                           data-prefetch-url="<?= Html::encode(Url::to("index/quickSearchPrefetch")) ?>"
                           data-search-url="<?= Html::encode(Url::to("index/suche", ["suchbegriff" => "SUCHBEGRIFF"])) ?>">
                    <button type="submit" class="btn btn-success" id="quicksearch_form_submit"><span class="glyphicon glyphicon-search"></span><span class="sr-only">Suchen</span>
                    </button>
                </form>

                <ul class="nav navbar-nav">
                    <li><a href="<?= Html::encode(Url::to("index/startseite")) ?>" style="font-weight: bold; color: white;">Startseite</a></li>
                    <!-- Desktop BA-wähler-->
                    <li class="dropdown ba-wahl-dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Bezirksausschüsse <span class="caret"></span></a>
                        <ul class="dropdown-menu" id="ba_nav_list">
                            <?
                            /** @var Bezirksausschuss[] $bas */
                            $bas = Bezirksausschuss::findAll();
                            foreach ($bas as $ba) echo "<li>" . Html::a($ba->ba_nr . ": " . $ba->name, $ba->getLink()) . "</li>\n"
                            ?>
                        </ul>
                    </li>
                    <!-- Mobiler BA-wähler-->
                    <li class="ba-wahl-link <? if ($this->context->top_menu == "bezirksausschuss") echo ' active'; ?>"><?= Html::a("Bezirksausschüsse", Url::to("index/bezirksausschuss")) ?></li>
                    <li  <? if ($this->context->top_menu == "benachrichtigungen") echo 'class="active"'; ?>><?= Html::a("Benachrichtigungen", Url::to("benachrichtigungen/index")) ?></li>
                    <li class="<? if ($this->context->top_menu == "themen") echo ' active'; ?>"><?= Html::a("Themen", Url::to("themen/index")) ?></li>
                    <li class="<? if ($this->context->top_menu == "termine") echo ' active'; ?>"><?= Html::a("Termine", Url::to("termine/index")) ?></li>
                    <li class="<? if ($this->context->top_menu == "personen") echo ' active'; ?>"><?= Html::a("Personen", Url::to("personen/index")) ?></li>
                    <?
                    $user = $this->context->aktuelleBenutzerIn();
                    if ($user && ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_CONTENT) || $user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_USER))) {
                        ?>
                        <li class="dropdown  <? if ($this->context->top_menu == "admin") echo 'active'; ?>">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <?
                                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_CONTENT)) { ?>
                                    <li><?= Html::a("StadträtInnen/Personen", Url::to("admin/stadtraetInnenPersonen")) ?></li>
                                    <li><?= Html::a("StadträtInnen: Social-Media-Daten", Url::to("admin/stadtraetInnenSocialMedia")) ?></li>
                                    <li><?= Html::a("StadträtInnen: Beschreibungen", Url::to("admin/stadtraetInnenBeschreibungen")) ?></li>
                                    <li><?= Html::a("BürgerInnenversammlungen", Url::to("admin/buergerInnenversammlungen")) ?></li>
                                <? }
                                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_USER)) { ?>
                                    <li><?= Html::a("StadträtInnen: Accounts", Url::to("admin/stadtraetInnenBenutzerInnen")) ?></li>
                                <? }
                                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_TAG)) { ?>
                                    <li><?= Html::a("Tags", Url::to("admin/tags")) ?></li>
                                <? }
                                ?>
                            </ul>
                        </li>
                    <? } ?>
                </ul>
            </div>
        </div>
    </div>

    <? if ($this->context->msg_ok != "") { ?>
    <div class="alert alert-success alert-dismissable " style="text-align: center">
        <?php echo $this->context->msg_ok; ?>
        <button type="button" class="close" data-dismiss="alert">×</button>
    </div>
    <? } ?>
    <? if ($this->context->msg_err != "") { ?>
    <div class="alert alert-danger alert-dismissable " style="text-align: center">
        <?php echo $this->context->msg_err; ?>
        <button type="button" class="close" data-dismiss="alert">×</button>
    </div>
    <? } ?>

    <div id="print_header">München Transparent - www.muenchen-transparent.de</div>

    <main class="container center-block row" id="page_main_content" <?
    if ($this->context->html_itemprop != "") echo 'itemscope itemtype="' . Html::encode($this->context->html_itemprop) . '"';
    ?>>
        <?php echo $content; ?>
    </main>
    <!-- /container -->

    <!-- Needed to keep the footer at the bottom -->
    <div class="footer_spacer"></div>
</div>
<!-- /over_footer_wrapper -->

<footer>
    <p class="container">
        <span class="pull-left">
            <?= Html::a("Über München-Transparent", Url::to("infos/ueber")) ?> /
            <?= Html::a("Anregungen?", Url::to("infos/feedback")) ?>
        </span>
        <span class="pull-right">
            <?= Html::a("Open-Source-Projekt <span class='hidden-xs'>(Github)</span>", "https://github.com/codeformunich/Muenchen-Transparent") ?> /
            <?= Html::a("Datenschutz", Url::to("infos/datenschutz")) ?> /
            <?= Html::a("Impressum", Url::to("infos/impressum")) ?>
        </span>
    </p>
</footer>
</body>
</html>
