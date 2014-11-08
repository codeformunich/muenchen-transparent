<?php
/**
 * @var $this RISBaseController
 * @var string $code
 * @var string $message
 */

$this->pageTitle = "Fehler";

switch ($code) {
	case 404:
		if ($message == "") $message = "Die gesuchte Seite gibt es nicht.";
		header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
		break;
	case 403:
		if ($message == "") $message = "Kein Zugriff auf diese Seite.";
		header($_SERVER["SERVER_PROTOCOL"] . ' 403 Forbidden');
		break;
	case 500:
		if ($message == "") $message = "Ein interner Fehler ist aufgetreten.";
		header($_SERVER["SERVER_PROTOCOL"] . ' 500 Internal Server Error');
		break;
}

?>
<section class="well">
	<h1>Leider ist ein Fehler aufgetreten</h1>

	<div class="alert alert-danger">
		<?= CHtml::encode($message) ?>
	</div>

</section>