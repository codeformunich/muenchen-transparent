<?php
/** @var IndexController $this */
?>
<!DOCTYPE html>
<html lang="de">
<head>
	<!-- Meta, title, CSS, favicons, etc. -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="Tobias Hößl">

	<!-- Bootstrap core CSS -->
	<link href="/js/bootstrap/_gh_pages/assets/css/bootstrap.css" rel="stylesheet">

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="/js/bootstrap/_gh_pages/assets/js/html5shiv.js"></script>
	<script src="/js/bootstrap/_gh_pages/assets/js/respond/respond.min.js"></script>
	<![endif]-->

	<!-- Favicons -->
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/js/bootstrap/_gh_pages/assets/ico/apple-touch-icon-144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/js/bootstrap/_gh_pages/assets/ico/apple-touch-icon-114-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/js/bootstrap/_gh_pages/assets/ico/apple-touch-icon-72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" href="/js/bootstrap/_gh_pages/assets/ico/apple-touch-icon-57-precomposed.png">
	<link rel="shortcut icon" href="/js/bootstrap/_gh_pages/assets/ico/favicon.png">


	<link rel="stylesheet" href="/js/Leaflet/dist/leaflet.css" />
	<!--[if lte IE 8]>
	<link rel="stylesheet" href="/js/Leaflet/dist/leaflet.ie.css" />
	<![endif]-->

	<script src="/js/Leaflet/dist/leaflet.js"></script>
	<script src="/js/leaflet.fullscreen/Control.FullScreen.js"></script>

</head>

<body>

<!-- Page content of course! -->
<!-- Custom styles for this template -->
<style>
		/* Move down content because we have a fixed navbar that is 50px tall */
	body {
		padding-top: 50px;
		padding-bottom: 20px;
	}

		/* Set widths on the navbar form inputs since otherwise they're 100% wide */
	.navbar-form input[type="text"],
	.navbar-form input[type="password"] {
		width: 180px;
	}

		/* Wrapping element */
		/* Set some basic padding to keep content from hitting the edges */
	.body-content {
		padding-left: 15px;
		padding-right: 15px;
	}

		/* Responsive: Portrait tablets and up */
	@media screen and (min-width: 768px) {
		/* Let the jumbotron breathe */
		.jumbotron {
			margin-top: 20px;
		}
		/* Remove padding from wrapping element since we kick in the grid classes here */
		.body-content {
			padding: 0;
		}
	}
</style>


<div class="clear"></div>


<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="container">
		<a class="navbar-toggle" data-toggle="collapse" data-target=".nav-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</a>
		<a class="navbar-brand" href="#"><?php echo CHtml::encode(Yii::app()->name); ?></a>
		<div class="nav-collapse collapse">
			<ul class="nav">
				<li class="active"><a href="#">Home</a></li>
				<li><a href="#about">About</a></li>
				<li><a href="#contact">Contact</a></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a href="#">Action</a></li>
						<li><a href="#">Another action</a></li>
						<li><a href="#">Something else here</a></li>
						<li class="divider"></li>
						<li class="nav-header">Nav header</li>
						<li><a href="#">Separated link</a></li>
						<li><a href="#">One more separated link</a></li>
					</ul>
				</li>
			</ul>
			<form class="navbar-form pull-right">
				<input type="text" placeholder="Email">
				<input type="password" placeholder="Password">
				<button type="submit" class="btn">Sign in</button>
			</form>
		</div><!--/.nav-collapse -->
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

</div> <!-- /container -->


<!-- Bootstrap core JavaScript
================================================== -->
<script src="/js/bootstrap/_gh_pages/assets/js/jquery.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-transition.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-alert.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-modal.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-dropdown.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-scrollspy.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-tab.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-tooltip.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-popover.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-button.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-collapse.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-carousel.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-typeahead.js"></script>
<script src="/js/bootstrap/_gh_pages/assets/js/bootstrap-affix.js"></script>

<script src="/js/bootstrap/_gh_pages/assets/js/holder/holder.js"></script>

<script src="/js/bootstrap/_gh_pages/assets/js/application.js"></script>


</body>
</html>
