<?php

/**
 * @var IndexController $this
 * @var string $current_url
 * @var string $msg_err
 */

?>
<section class="col-md-4 col-md-offset-4">
	<?
	if ($msg_err != "") {
		?>
		<div class="alert alert-danger">
			<?php echo $msg_err; ?>
		</div>
	<?
	}
	?>
	<div class="well">
		<form class="form-horizontal form-signin" method="POST" action="<?= CHtml::encode($current_url) ?>">
			<fieldset>
				<legend class="form_row">Passwort zurücksetzen</legend>

				<div class="form_row">
					<label for="email" class="control-label sr-only">E-Mail-Adresse</label>
					<input id="email" type="email" name="email" class="form-control" placeholder="Email-Adresse" autofocus required>
				</div>

				<button class="btn btn-lg btn-primary btn-block" type="submit" name="<?php echo AntiXSS::createToken("pwd_reset"); ?>">Zurücksetzen</button>
			</fieldset>
		</form>
	</div>
</section>