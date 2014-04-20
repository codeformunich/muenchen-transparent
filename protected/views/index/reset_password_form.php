<?php

/**
 * @var IndexController $this
 * @var string $current_url
 * @var string $msg_err
 */

?>

<form class="form-signin" method="POST" action="<?= CHtml::encode($current_url) ?>">
	<h1 class="form-signin-heading">Passwort zurücksetzen</h1>
	<?
	if ($msg_err != "") {
		?>
		<div class="alert alert-danger">
			<?php echo $msg_err; ?>
		</div>
	<?
	}
	?>

	<input id="email" type="email" name="email" class="form-control" placeholder="Email-Adresse" autofocus required>

	<button class="btn btn-lg btn-primary btn-block" type="submit" name="<?php echo AntiXSS::createToken("pwd_reset"); ?>">Passwort zurücksetzen</button>
</form>
