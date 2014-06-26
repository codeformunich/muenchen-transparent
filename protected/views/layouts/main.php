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

	<link href="/js/bootstrap-3.2.0/css/bootstrap.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" media="screen" href="/js/bootstrap-select/bootstrap-select.min.css">

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

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

	<? if ($this->load_leaflet_css) { ?>
		<link rel="stylesheet" href="/js/Leaflet/leaflet.css"/>
	<?
	}
	if ($this->load_leaflet_draw_css) {
		?>
		<link rel="stylesheet" href="/js/Leaflet.draw-0.2.3/dist/leaflet.draw.css"/>
	<? }
	?>

	<link rel="stylesheet" href="/css/jquery-ui-1.10.4.custom.min.css"/>
	<link rel="stylesheet" href="/css/styles.css"/>

	<!--[if lt IE 9]>
	<script src="/js/jquery-1.11.1.min.js"></script>
	<![endif]-->
	<!--[if gte IE 9]><!-->
	<script src="/js/jquery-2.1.1.min.js"></script>
	<!--<![endif]-->

	<script src="/js/jquery-ui-1.10.4.custom.min.js"></script>
	<script src="/js/modernizr.js"></script>
	<script src="/js/scrollintoview.js"></script>
	<script src="/js/antraegekarte.jquery.js"></script>
</head>

<body>

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
				<li <? if ($this->top_menu == "stadtrat") echo 'class="active"'; ?>><?= CHtml::link("Stadtrat", $this->createUrl("index/index")) ?></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Stadtteile / BAs <b class="caret"></b></a>
					<ul class="dropdown-menu" id="ba_nav_list">
						<?
						/** @var Bezirksausschuss[] $bas */
						$bas = Bezirksausschuss::model()->findAll();
						foreach ($bas as $ba) echo "<li>" . CHtml::link($ba->ba_nr . ": " . $ba->name, $this->createUrl("index/ba", array("ba_nr" => $ba->ba_nr))) . "</li>\n"
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
				<li <? if ($this->top_menu == "infos") echo 'active'; ?>><?= CHtml::link("Infos", $this->createUrl("index/infos")) ?></li>
			</ul>

			<form class="navbar-form navbar-right" method="POST" action="<?= CHtml::encode($this->createUrl("index/suche")) ?>">
				<div class="form-group">
					<input type="text" name="suchbegriff" placeholder="Volltextsuche" class="form-control">
				</div>
				<button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-search"></span></button>
			</form>
		</div>
	</div>
</div>

<div class="container">
	<div class="body-content">

		<?php echo $content; ?>

		<hr>


		<footer>
			<p><?=CHtml::link("Datenschutzerklärung", Yii::app()->createUrl("index/datenschutz"))?>
				&nbsp;
				<?=CHtml::link("Impressum", Yii::app()->createUrl("index/impressum"))?></p>
		</footer>
	</div>

</div>
<!-- /container -->


<!-- Bootstrap core JavaScript
================================================== -->
<script src="/js/bootstrap-3.2.0/js/bootstrap.min.js"></script>
<script src="/js/bootstrap-select/bootstrap-select.min.js"></script>

</body>
</html>
