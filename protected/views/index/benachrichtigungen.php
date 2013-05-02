<?php
/**
 * @var $this IndexController
 * @var $error array
 * @var string $code
 * @var string $message
 * @var BenutzerIn $ich
 * @var string $msg_ok
 * @var string $msg_err
 */

$this->pageTitle = "Benachrichtigungen";
?>

<h1>Benachrichtigungen</h1>


<?
if ($msg_ok != "") {
	?>
	<div class="alert alert-success">
		<?php echo $msg_ok; ?>
	</div>
<?
}
if ($msg_err != "") {
	?>
	<div class="alert alert-error">
		<?php echo $msg_err; ?>
	</div>
<?
}

?>

<h2>Aktive Benachrichtigungen</h2>
<form method="POST" action="<?= CHtml::encode($this->createUrl("index/benachrichtigungen")) ?>">
	<ul>
		<?
		$bens = $ich->getBenachrichtigungen();
		foreach ($bens as $ben) {
			echo "<li>";
			echo "<label><input type='checkbox' name='del_ben[]' value='" . CHtml::encode(json_encode($ben->krits)) . "'> " . $ben->getTitle() . "</label>";
			echo "</li>";
		}
		?>
	</ul>
	<button type="submit" class="btn btn-danger" name="<?= AntiXSS::createToken("del") ?>">Ausgewählte Benachrichtigungen löschen</button>
</form>