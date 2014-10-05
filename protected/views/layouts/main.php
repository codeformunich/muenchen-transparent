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
	<meta name="description" content="">
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
<div class="over_footer_wrapper">
<div class="clear"></div>

<div class="navbar navbar-inverse navbar-fixed-top" id="main_navbar">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		<div class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li><a href="<?=CHtml::encode(Yii::app()->createUrl("index/startseite"))?>" style="font-weight: bold; color: white;">[TODO: Logo]</a></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Stadtteile / BAs <b class="caret"></b></a>
					<ul class="dropdown-menu" id="ba_nav_list">
						<?
						/** @var Bezirksausschuss[] $bas */
						$bas = Bezirksausschuss::model()->findAll();
						foreach ($bas as $ba) echo "<li>".CHtml::link($ba->ba_nr.": ".$ba->name, $this->createUrl("index/ba", array("ba_nr" => $ba->ba_nr)))."</li>\n"
						?>
					</ul>
				</li>
				<li  <? if ($this->top_menu == "benachrichtigungen") echo 'class="active"'; ?>><?= CHtml::link("Benachrichtigungen", $this->createUrl("benachrichtigungen/index")) ?></li>
				<? if (Yii::app()->user->getState("role") == "admin") { ?>
					<li class="dropdown  <? if ($this->top_menu == "admin") echo 'active'; ?>">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><?= CHtml::link("StadträtInnen/Personen", $this->createUrl("admin/stadtraetInnenPersonen")) ?></li>
							<li><?= CHtml::link("StadträtInnen: Social-Media-Daten", $this->createUrl("admin/stadtraetInnenSocialMedia")) ?></li>
						</ul>
					</li>
				<? } ?>
				<li class="<? if ($this->top_menu == "themen") echo ' active'; ?>"><?= CHtml::link("Themen", $this->createUrl("themen/index")) ?></li>
				<li class="<? if ($this->top_menu == "termine") echo ' active'; ?>"><?= CHtml::link("Termine", $this->createUrl("termine/index")) ?></li>
				<li class="<? if ($this->top_menu == "personen") echo ' active'; ?>"><?= CHtml::link("Personen", $this->createUrl("infos/personen")) ?></li>
			</ul>

			<form class="navbar-form navbar-right" method="POST" action="<?= CHtml::encode($this->createUrl("index/suche")) ?>" id="quicksearch_form">
				<div class="form-group">
					<input type="text" name="suchbegriff" value="<?= CHtml::encode($this->suche_pre) ?>" placeholder="Volltextsuche" class="form-control"
						   data-prefetch-url="<?=CHtml::encode($this->createUrl("index/quickSearchPrefetch"))?>"
						   data-search-url="<?=CHtml::encode($this->createUrl("index/suche", array("suchbegriff" => "SUCHBEGRIFF")))?>">
				</div>
				<button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-search"></span></button>
			</form>
		</div>
	</div>
</div>

<div class="container">
	<div class="body-content">

		<?php echo $content; ?>
	</div>
</div>
<!-- /container -->

<!-- Needed to keep the footer at the bottom -->
<div class="footer_spacer"></div>
</div> <!-- /over_footer_wrapper -->

<footer>
	<hr>
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
<script src="/js/bootstrap-select/bootstrap-select.min.js"></script>
<script src="/js/typeahead.js/typeahead.bundle.min.js"></script>
<script src="/js/index.js"></script>

</body>
</html>
