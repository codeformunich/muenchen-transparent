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
	<meta name="description" content="<?
	if ($this->html_description != "") echo CHtml::encode($this->html_description);
	else echo "Münchens Stadtpolitik einfach erklärt. Aktuelle Entscheidungen und Dokumente im alternativen Ratsinformationssystem.";
	?>">
	<meta name="author" content="Tobias Hößl, Konstantin Schütze">

	<link rel="search" type="application/opensearchdescription+xml" title="Ratsinformant" href="/other/OpenSearch.xml">
	<link rel="icon" type="image/png" href="/css/img/logo.png">

	<title><?php
		echo CHtml::encode($this->pageTitle);
		if (strpos($this->pageTitle, "Transparent") === false) echo " (" . CHtml::encode(Yii::app()->params['projectTitle']) . ")";
		?></title>


    <?
    if ($this->load_leaflet_css) echo '<link rel="stylesheet" href="/js/Leaflet/leaflet.css"/>';
    if ($this->load_leaflet_draw_css) echo '<link rel="stylesheet" href="/js/Leaflet.draw-0.2.3/dist/leaflet.draw.css"/>';
    if ($this->load_calendar) echo '<link rel="stylesheet" href="/js/fullcalendar-2.1.1/fullcalendar.min.css"/>';
    if ($this->load_selectize_js) echo '<link rel="stylesheet" href="/css/selectizejs.ratsinformant.css"/>';

    if ($this->load_pdf_js) { ?>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="google" content="notranslate">
        <link rel="stylesheet" href="/other/pdfjs/viewer.css"/>

    <? } ?>

    <link rel="stylesheet" href="/css/jquery-ui-1.11.2.custom.min.css"/>
    <link rel="stylesheet" href="/css/styles_website.css">


    <!--[if lt IE 9]>
    <script src="/js/jquery-1.11.1.min.js"></script>
    <![endif]-->
	<!--[if gte IE 9]><!-->
	<script src="/js/jquery-2.1.1.min.js"></script>
	<!--<![endif]-->

	<!--[if lt IE 9]>
	<script src="/js/html5shiv.js"></script>
	<script src="/js/respond.min.js"></script>
	<![endif]-->

	<? if ($this->load_pdf_js) { ?>
		<script src="/other/pdfjs/compatibility.js"></script>

		<!-- This snippet is used in production (included from viewer.html) -->
		<link rel="resource" type="application/l10n" href="/other/pdfjs/locale/locale.properties"/>
		<script src="/other/pdfjs/l10n.js"></script>
		<script src="/other/pdfjs/pdf.js"></script>

		<script src="/other/pdfjs/debugger.js"></script>
		<script src="/other/pdfjs/viewer.js"></script>

		<script>
		// Fix the problem that pdf js doesn't get the height automatically (maybe because of the footer)
		function pdf_resize() {
			$("#mainContainer").height($("footer").offset().top - $("#mainContainer").offset().top - 20);
		}

		$(pdf_resize);
		$(window).resize(pdf_resize);
		</script>
	<? } // end pdf.js ?>
</head>

<body>
<script src="/js/modernizr.js"></script>
<?
echo ris_intern_html_extra_headers();
?>

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
				<ul class="nav navbar-nav">
					<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>" style="font-weight: bold; color: white;">Startseitemod</a></li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Stadtteile / BAs <span class="caret"></span></a>
						<ul class="dropdown-menu" id="ba_nav_list">
							<?
							/** @var Bezirksausschuss[] $bas */
							$bas = Bezirksausschuss::model()->findAll();
							foreach ($bas as $ba) echo "<li>" . CHtml::link($ba->ba_nr . ": " . $ba->name, $ba->getLink()) . "</li>\n"
							?>
						</ul>
					</li>
					<li  <? if ($this->top_menu == "benachrichtigungen") echo 'class="active"'; ?>><?= CHtml::link("Benachrichtigungen", $this->createUrl("benachrichtigungen/index")) ?></li>
					<li class="<? if ($this->top_menu == "themen") echo ' active'; ?>"><?= CHtml::link("Themen", $this->createUrl("themen/index")) ?></li>
					<li class="<? if ($this->top_menu == "termine") echo ' active'; ?>"><?= CHtml::link("Termine", $this->createUrl("termine/index")) ?></li>
					<li class="<? if ($this->top_menu == "personen") echo ' active'; ?>"><?= CHtml::link("Personen", $this->createUrl("index/personen")) ?></li>
					<?
					if ($this->binContentAdmin()) {
						?>
						<li class="dropdown  <? if ($this->top_menu == "admin") echo 'active'; ?>">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><?= CHtml::link("StadträtInnen/Personen", $this->createUrl("admin/stadtraetInnenPersonen")) ?></li>
								<li><?= CHtml::link("StadträtInnen: Social-Media-Daten", $this->createUrl("admin/stadtraetInnenSocialMedia")) ?></li>
							</ul>
						</li>
					<? } ?>
				</ul>

				<form class="navbar-form navbar-right" method="POST" action="<?= CHtml::encode($this->createUrl("index/suche")) ?>" id="quicksearch_form">
					<label for="quicksearch_form_input" style="display: none;">Volltextsuche - Suchbegriff:</label>
					<input type="text" name="suchbegriff" value="<?= CHtml::encode($this->suche_pre) ?>" placeholder="Volltextsuche" class="form-control"
						   id="quicksearch_form_input"
						   data-prefetch-url="<?= CHtml::encode($this->createUrl("index/quickSearchPrefetch")) ?>"
						   data-search-url="<?= CHtml::encode($this->createUrl("index/suche", array("suchbegriff" => "SUCHBEGRIFF"))) ?>">
					<button type="submit" class="btn btn-success" id="quicksearch_form_submit"><span class="glyphicon glyphicon-search"></span><span class="sr-only">Suchen</span>
					</button>
				</form>
			</div>
		</div>
	</div>

	<main class="container center-block row" id="page_main_content">
		<?php echo $content; ?>
	</main>
	<!-- /container -->

	<!-- Needed to keep the footer at the bottom -->
	<div class="footer_spacer"></div>
</div><!-- /over_footer_wrapper -->

<footer>
	<p class="container">
		<span class="pull-left">
			<?= CHtml::link("Über München-Transparent", Yii::app()->createUrl("infos/ueber")) ?> /
			<?= CHtml::link("Anregungen?", Yii::app()->createUrl("infos/feedback")) ?>
		</span>
		<span class="pull-right">
			<?= CHtml::link("Open-Source-Projekt (Github)", "https://github.com/codeformunich/Ratsinformant") ?> /
			<?= CHtml::link("Datenschutz", Yii::app()->createUrl("infos/datenschutz")) ?> /
			<?= CHtml::link("Impressum", Yii::app()->createUrl("infos/impressum")) ?>
		</span>
	</p>
</footer>

<script src="/js/jquery-ui-1.11.2.custom.min.js"></script>
<script src="/js/scrollintoview.js"></script>
<script src="/js/antraegekarte.jquery.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/material/ripples.min.js"></script>
<script src="/js/material/material.min.js"></script>
<script src="/js/typeahead.js/typeahead.bundle.min.js"></script>
<script src="/js/index.js"></script>
<?
if ($this->load_selectize_js) echo '<script src="/js/selectize.js-0.11.2/dist/js/standalone/selectize.min.js"></script>';
?>
</body>
</html>
