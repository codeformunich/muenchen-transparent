<?php

/**
 * @var IndexController $this
 * @var string $current_url
 * @var string $msg_err
 */

?>

<form class="form-signin" method="POST" action="<?= CHtml::encode($current_url) ?>">
	<h1 class="form-signin-heading">Neues Passwort setzen</h1>
	<?
	if ($msg_err != "") {
		?>
		<div class="alert alert-danger">
			<?php echo $msg_err; ?>
		</div>
	<?
	}
	?>

	<input id="password" type="password" name="password" class="form-control" placeholder="Passwort" required>
	<input id="password2" type="password" name="password2" class="form-control" placeholder="Passwort bestätigen">

	<button class="btn btn-lg btn-primary btn-block" type="submit" name="<?php echo AntiXSS::createToken("set"); ?>">Passwort setzen</button>
</form>
