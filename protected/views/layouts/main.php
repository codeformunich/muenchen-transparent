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
	<meta name="author" content="Tobias Hößl">

	<link rel="search" type="application/opensearchdescription+xml" title="Ratsinformant" href="/other/OpenSearch.xml">

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

	<!--[if lt IE 9]>
	<script src="/js/html5shiv.js"></script>
	<script src="/js/respond.min.js"></script>
	<![endif]-->

	<? if ($this->load_leaflet_css) { ?>
		<link rel="stylesheet" href="/js/Leaflet/leaflet.css"/>
	<?
	}
	if ($this->load_leaflet_draw_css) {
		?>
		<link rel="stylesheet" href="/js/Leaflet.draw-0.2.3/dist/leaflet.draw.css"/>
	<?
	}
	?>

	<link rel="stylesheet" href="/css/jquery-ui-1.11.1.custom.min.css"/>
	<link rel="stylesheet" href="/css/styles.css">

	<!--[if lt IE 9]>
	<script src="/js/jquery-1.11.1.min.js"></script>
	<![endif]-->
	<!--[if gte IE 9]><!-->
	<script src="/js/jquery-2.1.1.min.js"></script>
	<!--<![endif]-->

	<script src="/js/modernizr.js"></script>
</head>

<body>
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
				<li><a href="<?=CHtml::encode(Yii::app()->createUrl("index/startseite"))?>" style="font-weight: bold; color: white;">[TODO: Logo]</a></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Stadtteile / BAs <span class="caret"></span></a>
					<ul class="dropdown-menu" id="ba_nav_list">
						<?
						/** @var Bezirksausschuss[] $bas */
						$bas = Bezirksausschuss::model()->findAll();
						foreach ($bas as $ba) echo "<li>".CHtml::link($ba->ba_nr.": ".$ba->name, $this->createUrl("index/ba", array("ba_nr" => $ba->ba_nr)))."</li>\n"
						?>
					</ul>
				</li>
				<li  <? if ($this->top_menu == "benachrichtigungen") echo 'class="active"'; ?>><?= CHtml::link("Benachrichtigungen", $this->createUrl("benachrichtigungen/index")) ?></li>
				<li class="<? if ($this->top_menu == "themen") echo ' active'; ?>"><?= CHtml::link("Themen", $this->createUrl("themen/index")) ?></li>
				<li class="<? if ($this->top_menu == "termine") echo ' active'; ?>"><?= CHtml::link("Termine", $this->createUrl("termine/index")) ?></li>
				<li class="<? if ($this->top_menu == "personen") echo ' active'; ?>"><?= CHtml::link("Personen", $this->createUrl("index/personen")) ?></li>
				<?
				if ($this->binContentAdmin()) { ?>
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
					<input type="text" name="suchbegriff" value="<?= CHtml::encode($this->suche_pre) ?>" placeholder="Volltextsuche" class="form-control" id="quicksearch_form_input"
						   data-prefetch-url="<?=CHtml::encode($this->createUrl("index/quickSearchPrefetch"))?>"
						   data-search-url="<?=CHtml::encode($this->createUrl("index/suche", array("suchbegriff" => "SUCHBEGRIFF")))?>">
				<button type="submit" class="btn btn-success" id="quicksearch_form_submit"><span class="glyphicon glyphicon-search"></span><span class="sr-only">Suchen</span></button>
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
</div> <!-- /over_footer_wrapper -->

<footer>
	<p class="container">
	<?= CHtml::link("Datenschutzerklärung", Yii::app()->createUrl("infos/datenschutz")) ?>
	&nbsp;
	<?= CHtml::link("Impressum", Yii::app()->createUrl("infos/impressum")) ?>
	</p>
</footer>

<script src="/js/jquery-ui-1.11.1.custom.min.js"></script>
<script src="/js/scrollintoview.js"></script>
<script src="/js/antraegekarte.jquery.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/material/ripples.min.js"></script>
<script src="/js/material/material.min.js"></script>
<script src="/js/typeahead.js/typeahead.bundle.min.js"></script>
<script src="/js/index.js"></script>

</body>
</html>
