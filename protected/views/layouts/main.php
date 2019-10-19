<?php
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
    <meta name="description" content="<?php
    if ($this->html_description != "") echo CHtml::encode($this->html_description);
    else echo "Münchens Stadtpolitik einfach erklärt. Aktuelle Entscheidungen und Dokumente im alternativen Ratsinformationssystem.";
    ?>">
    <meta name="author" content="Tobias Hößl, Konstantin Schütze">

    <link rel="apple-touch-icon" sizes="57x57" href="/icons/apple-touch-icon-57x57.png?1">
    <link rel="apple-touch-icon" sizes="60x60" href="/icons/apple-touch-icon-60x60.png?1">
    <link rel="apple-touch-icon" sizes="72x72" href="/icons/apple-touch-icon-72x72.png?1">
    <link rel="apple-touch-icon" sizes="76x76" href="/icons/apple-touch-icon-76x76.png?1">
    <link rel="apple-touch-icon" sizes="114x114" href="/icons/apple-touch-icon-114x114.png?1">
    <link rel="apple-touch-icon" sizes="120x120" href="/icons/apple-touch-icon-120x120.png?1">
    <link rel="apple-touch-icon" sizes="144x144" href="/icons/apple-touch-icon-144x144.png?1">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/apple-touch-icon-152x152.png?1">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon-180x180.png?1">
    <link rel="icon" type="image/png" href="/icons/favicon-96x96.png?1" sizes="96x96">
    <link rel="icon" type="image/png" href="/icons/favicon-16x16.png?1" sizes="16x16">
    <link rel="icon" type="image/png" href="/icons/favicon-32x32.png?1" sizes="32x32">
    <meta name="msapplication-TileColor" content="#0f9d58">
    <meta name="msapplication-TileImage" content="/icons/mstile-144x144.png?1">
    <meta property="og:image" content="/images/fb-img2.png">

    <link rel="search" type="application/opensearchdescription+xml" title="<?= CHtml::encode(Yii::app()->params['projectTitle']) ?>" href="/OpenSearch.xml">

    <title><?php
        echo CHtml::encode($this->pageTitle);
        if (strpos($this->pageTitle, "Transparent") === false) echo " (" . CHtml::encode(Yii::app()->params['projectTitle']) . ")";
        ?></title>

    <!-- css -->

    <link rel="stylesheet" href="/css/build/website.css">

    <?php
    if ($this->load_mediaelement) echo '<link rel="stylesheet" href="/bower/mediaelement/build/mediaelementplayer.min.css">';
    if ($this->load_calendar    ) echo '<link rel="stylesheet" href="/bower/fullcalendar/dist/fullcalendar.min.css">';
    if ($this->load_selectize_js) echo '<link rel="stylesheet" href="/css/selectizejs.ratsinformant.css">';
    if ($this->load_leaflet     ) echo '<link rel="stylesheet" href="/bower/leaflet.draw/dist/leaflet.draw.css">';

    if ($this->load_pdf_js) { ?>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="google" content="notranslate">
        <link rel="stylesheet" href="/pdfjs/web/build.css"/>
    <?php } ?>

    <?php if ($this->inline_css != "") {
        echo '<style>' . $this->inline_css . '</style>';
    } ?>

    <!-- javascript -->
    <script src="/js/build/std.js"></script>

    <?php
    if ($this->load_ckeditor     ) echo '<script src="/bower/ckeditor/ckeditor.js"></script>';
    if ($this->load_isotope_js   ) echo '<script src="/bower/isotope/dist/isotope.pkgd.min.js"></script>';
    if ($this->load_list_js      ) echo '<script src="/bower/list.js/dist/list.min.js"></script>';
    if ($this->load_mediaelement ) echo '<script src="/bower/mediaelement/build/mediaelement-and-player.min.js" defer></script>';
    if ($this->load_selectize_js ) echo '<script src="/bower/selectize/dist/js/standalone/selectize.min.js" defer></script>';
    if ($this->load_shariff      ) echo '<script src="/bower/shariff/build/shariff.min.js" defer></script>';
    ?>

    <?php if ($this->load_calendar) { ?>
        <script src="/bower/moment/min/moment-with-locales.min.js"></script>
        <script src="/bower/fullcalendar/dist/fullcalendar.min.js"></script>
        <script src="/bower/fullcalendar/dist/lang/de.js"></script>
    <?php } ?>

    <?php if ($this->load_pdf_js) { ?>
        <link rel="preload" type="application/l10n" href="/pdfjs/web/locale/locale.properties"/>
        <script src="/pdfjs/web/build.js" defer></script>
    <?php }

    if ($this->load_leaflet) { ?>
        <script src="/js/build/leaflet.js"></script>
    <?php } ?>

</head>

<body>

<script src="/js/modernizr.js"></script>
<?php echo ris_intern_html_extra_headers(); ?>

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
                <form class="navbar-form navbar-right" method="POST" action="<?= CHtml::encode($this->createUrl("index/suche")) ?>" id="quicksearch_form">
                    <label for="quicksearch_form_input" style="display: none;">Volltextsuche - Suchbegriff:</label>
                    <input type="text" name="suchbegriff" value="<?= CHtml::encode($this->suche_pre) ?>" placeholder="Volltextsuche" class="form-control"
                           id="quicksearch_form_input" required
                           data-prefetch-url="<?= CHtml::encode($this->createUrl("index/quickSearchPrefetch")) ?>"
                           data-search-url="<?= CHtml::encode($this->createUrl("index/suche", ["suchbegriff" => "SUCHBEGRIFF"])) ?>">
                    <button type="submit" class="btn btn-success" id="quicksearch_form_submit"><span class="glyphicon glyphicon-search"></span><span class="sr-only">Suchen</span>
                    </button>
                </form>

                <ul class="nav navbar-nav">
                    <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>" style="font-weight: bold; color: white;">Startseite</a></li>
                    <!-- Desktop BA-wähler-->
                    <li class="dropdown ba-wahl-dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Bezirksausschüsse <span class="caret"></span></a>
                        <ul class="dropdown-menu" id="ba_nav_list">
                            <?php
                            /** @var Bezirksausschuss[] $bas */
                            $bas = Bezirksausschuss::model()->alleOhneStadtrat();
                            foreach ($bas as $ba) echo "<li>" . CHtml::link($ba->ba_nr . ": " . $ba->name, $ba->getLink()) . "</li>\n"
                            ?>
                        </ul>
                    </li>
                    <!-- Mobiler BA-wähler-->
                    <li class="ba-wahl-link <?php if ($this->top_menu == "bezirksausschuss") echo ' active'; ?>"><?= CHtml::link("Bezirksausschüsse", $this->createUrl("index/bezirksausschuss")) ?></li>
                    <li <?php if ($this->top_menu == "benachrichtigungen") echo 'class="active"'; ?>><?= CHtml::link("Benachrichtigungen", $this->createUrl("benachrichtigungen/index")) ?></li>
                    <li class="<?php if ($this->top_menu == "termine") echo ' active'; ?>"><?= CHtml::link("Termine", $this->createUrl("termine/index")) ?></li>
                    <li class="<?php if ($this->top_menu == "personen") echo ' active'; ?>"><?= CHtml::link("Personen", $this->createUrl("personen/index")) ?></li>
                    <?php
                    $user = $this->aktuelleBenutzerIn();
                    if ($user && $user->hatIrgendeineBerechtigung()) {
                    ?>
                        <li class="dropdown <?php if ($this->top_menu == "admin") echo 'active'; ?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li class="<?php if ($this->top_menu == "themen") echo ' active'; ?>"><?= CHtml::link("Themen", $this->createUrl("themen/index")) ?></li>
                            <?php
                                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_CONTENT)) { ?>
                                    <li><?= CHtml::link("StadträtInnen/Personen", $this->createUrl("admin/stadtraetInnenPersonen")) ?></li>
                                    <li><?= CHtml::link("StadträtInnen: Social-Media-Daten", $this->createUrl("admin/stadtraetInnenSocialMedia")) ?></li>
                                    <li><?= CHtml::link("StadträtInnen: Beschreibungen", $this->createUrl("admin/stadtraetInnenBeschreibungen")) ?></li>
                                    <li><?= CHtml::link("BürgerInnenversammlungen", $this->createUrl("admin/buergerInnenversammlungen")) ?></li>
                                <?php }
                                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_USER)) { ?>
                                    <li><?= CHtml::link("StadträtInnen: Accounts", $this->createUrl("admin/stadtraetInnenBenutzerInnen")) ?></li>
                                <?php }
                                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_TAG)) { ?>
                                    <li><?= CHtml::link("Tags", $this->createUrl("admin/tags")) ?></li>
                                <?php }
                                ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Mehr <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><?= CHtml::link("Glossar", $this->createUrl("infos/glossar")) ?></li>
                            <li><?= CHtml::link("Themen", $this->createUrl("themen/index")) ?></li>
                            <li><?= CHtml::link("So funktioniert Stadtpolitik", $this->createUrl("infos/soFunktioniertStadtpolitik")) ?></li>
                            <li><?= CHtml::link("Satzungen und Verordnung", $this->createUrl("infos/stadtrecht")) ?></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?php if ($this->msg_ok != "") { ?>
    <div class="alert alert-success alert-dismissable " style="text-align: center">
        <?php echo $this->msg_ok; ?>
        <button type="button" class="close" data-dismiss="alert">×</button>
    </div>
    <?php } ?>
    <?php if ($this->msg_err != "") { ?>
    <div class="alert alert-danger alert-dismissable " style="text-align: center">
        <?php echo $this->msg_err; ?>
        <button type="button" class="close" data-dismiss="alert">×</button>
    </div>
    <?php } ?>

    <div id="print_header">München Transparent - www.muenchen-transparent.de</div>

    <main class="container center-block row" id="page_main_content" <?php
    if ($this->html_itemprop != "") echo 'itemscope itemtype="' . CHtml::encode($this->html_itemprop) . '"';
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
            <?= CHtml::link("Über München-Transparent", Yii::app()->createUrl("infos/ueber")) ?> /
            <?= CHtml::link("Anregungen?", Yii::app()->createUrl("infos/feedback")) ?> /
            <a href="https://meine-stadt-transparent.de/info/about/"
               title="Meine Stadt Transparent - ein freies Ratsinformationssystem ähnlich München Transparent">Weitere Städte</a>
        </span>
        <span class="pull-right">
            <?= CHtml::link("Open-Source-Projekt", "https://github.com/codeformunich/Muenchen-Transparent") ?> /
            <?= CHtml::link("API", Yii::app()->createUrl("infos/api")) ?> /
            <?= CHtml::link("Datenschutz", Yii::app()->createUrl("infos/datenschutz")) ?> /
            <?= CHtml::link("Impressum", Yii::app()->createUrl("infos/impressum")) ?>
        </span>
    </p>
</footer>
</body>
</html>
