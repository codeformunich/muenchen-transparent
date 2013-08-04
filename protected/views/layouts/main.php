<?php
/** @var RISBaseController $this */
?>
<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="Tobias Hößl">

	<link href="/js/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="/css/bootstrap-glyphicons.css" rel="stylesheet">

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="/js/html5shiv.js"></script>
	<script src="/js/respond.min.js"></script>
	<![endif]-->

	<!--
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/js/bootstrap/bootstrap//ico/apple-touch-icon-144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/js/bootstrap/bootstrap//ico/apple-touch-icon-114-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/js/bootstrap/bootstrap//ico/apple-touch-icon-72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" href="/js/bootstrap/bootstrap//ico/apple-touch-icon-57-precomposed.png">
	<link rel="shortcut icon" href="/js/bootstrap/bootstrap//ico/favicon.png">
	-->

	<!--
<link rel="stylesheet" href="/js/Leaflet.markercluster/dist/MarkerCluster.css" />
<link rel="stylesheet" href="/js/Leaflet.markercluster/dist/MarkerCluster.Default.css" />
-->


	<? if ($this->load_leaflet_css) { ?>
		<link rel="stylesheet" href="/js/Leaflet/leaflet.css"/>
		<!--[if lte IE 8]>
		<link rel="stylesheet" href="/js/Leaflet/leaflet.ie.css"/>
		<![endif]-->
	<?
	}
	if ($this->load_leaflet_draw_css) {
		?>
		<link rel="stylesheet" href="/js/Leaflet.draw/dist/leaflet.draw.css"/>
		<!--[if lte IE 8]>
		<link rel="stylesheet" href="/js/Leaflet.draw/dist/leaflet.draw.ie.css"/>
		<![endif]-->
	<? }
	?>

	<link rel="stylesheet" href="/css/jquery-ui-1.10.3.custom.min.css"/>
	<link rel="stylesheet" href="/styles.css"/>

	<script src="/js/jquery-1.10.2.min.js"></script>
	<script src="/js/jquery-ui-1.10.3.custom.min.js"></script>
	<script src="/js/modernizr.js"></script>
	<script src="/js/scrollintoview.js"></script>
	<script src="/js/antraegekarte.jquery.js"></script>
</head>

<body>

<div class="clear"></div>


<div class="navbar navbar-inverse navbar-fixed-top" id="main_navbar">
	<div class="container">
		<a class="navbar-toggle" data-toggle="collapse" data-target=".nav-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</a>
		<a class="navbar-brand" href="<?= CHtml::encode($this->createUrl("index/index")) ?>"><?php echo CHtml::encode(Yii::app()->name); ?></a>

		<div class="nav-collapse collapse">
			<ul class="nav navbar-nav">
				<li <? if ($this->top_menu == "stadtrat") echo 'class="active"'; ?>><?= CHtml::link("Stadtrat", $this->createUrl("index/stadtrat")) ?></li>
				<li class="dropdown  <? if ($this->top_menu == "ba") echo 'active'; ?>">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Bezirksausschüsse <b class="caret"></b></a>
					<ul class="dropdown-menu" id="ba_nav_list">
						<?
						/** @var Bezirksausschuss[] $bas */
						$bas = Bezirksausschuss::model()->findAll();
						foreach ($bas as $ba) echo "<li>" . CHtml::link($ba->ba_nr . ": " . $ba->name, $this->createUrl("index/ba", array("ba_nr" => $ba->ba_nr))) . "</li>\n"
						?>
					</ul>
				</li>
				<li  <? if ($this->top_menu == "benachrichtigungen") echo 'class="active"'; ?>><?= CHtml::link("Benachrichtigungen", $this->createUrl("benachrichtigungen/index")) ?></li>
				<li class="dropdown  <? if ($this->top_menu == "admin") echo 'active'; ?>">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><?= CHtml::link("StadträtInnen/Personen", $this->createUrl("admin/stadtraetInnenPersonen")) ?></li>
					</ul>
				</li>
			</ul>
			<form class="navbar-form form-inline pull-right" method="POST" action="<?= CHtml::encode($this->createUrl("index/suche")) ?>">
				<input type="text" name="suchbegriff" placeholder="Volltextsuche">
				<button type="submit" class="btn"><span class="glyphicon glyphicon-search"></span></button>
			</form>
		</div>
		<!--/.nav-collapse -->
	</div>
</div>

<div class="container">
	<div class="body-content">

		<?php echo $content; ?>

		<hr>


		<footer>
			<p><a href="https://www.hoessl.eu/impressum/">Impressum</a></p>
		</footer>
	</div>

</div>
<!-- /container -->


<!-- Bootstrap core JavaScript
================================================== -->
<script src="/js/bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
