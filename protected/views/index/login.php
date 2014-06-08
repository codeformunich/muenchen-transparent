<?php

/**
 * @var IndexController $this
 * @var string $current_url
 * @var string $msg_ok
 * @var string $msg_err
 */

?>

<form class="form-signin login" method="POST" action="<?= CHtml::encode($current_url) ?>">
	<?
	foreach ($_POST as $key => $val) if (!in_array($key, array("email", "bestaetigungscode", "password", "password2", AntiXSS::createToken("login"), AntiXSS::createToken("anlegen")))) {
		echo "<input type='hidden' name='" . CHtml::encode($key) . "' value='" . CHtml::encode($val) . "'>";
	}
	?>
	<h1 class="form-signin-heading">Einloggen</h1>

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
		<div class="alert alert-danger">
			<?php echo $msg_err; ?>
		</div>
	<?
	}
	?>

	<label class="checkbox">
		<input type="checkbox" name="register" id="create_account"> Neuen Zugang anlegen
	</label>

	<input id="email" type="email" name="email" class="form-control" placeholder="Email-Adresse" autofocus required>
	<input id="password" type="password" name="password" class="form-control" placeholder="Passwort" required>
	<input id="password2" type="password" name="password2" class="form-control" placeholder="Passwort bestätigen">

	<div id="bestaetigungscode_holder" style="display: none;">
		Es wurde bereits eine E-Mail mit dem Bestätigungscode an diese Adresse geschickt.<br>
		<label for="bestaetigungscode"><strong>Bitte gib den Bestätigungscode an:</strong></label>

		<div>
			<input type="text" name="bestaetigungscode" id="bestaetigungscode" value="" style="width: 280px;">
		</div>
		<br><br><br>
	</div>

	<div style="text-align: right; font-style: italic; margin-top: -5px; font-size: 11px; margin-bottom: 7px;">
		<?php echo CHtml::link("Passwort vergessen?", $this->createUrl("index/resetPasswordForm")) ?>
	</div>

	<button class="btn btn-lg btn-primary btn-block" type="submit" name="<?php echo AntiXSS::createToken("login_anlegen"); ?>"><span class="login">Login</span><span class="anlegen">Anlegen</span></button>
</form>

<script>
	$(function () {
		var $form = $("form.form-signin");
		$("#create_account").on("click change", function () {
			if ($(this).prop("checked")) {
				$form.removeClass("login").addClass("anlegen");
			} else {
				$form.removeClass("anlegen").addClass("login");
			}
		});
	})
</script>