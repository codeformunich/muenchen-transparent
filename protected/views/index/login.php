<?php

/**
 * @var IndexController $this
 * @var string $current_url
 * @var string $msg_ok
 * @var string $msg_err
 */

?>

<h1>Login</h1>

<hr>


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

<form method="POST" style="margin-left: auto; margin-right: auto; width: 310px; overflow: auto;" action="<?= CHtml::encode($current_url) ?>">
	<?
	foreach ($_POST as $key=>$val) if (!in_array($key, array("email", "bestaetigungscode", "password", AntiXSS::createToken("login")))) {
		echo "<input type='hidden' name='" . CHtml::encode($key) . "' value='" . CHtml::encode($val) . "'>";
	}
	?>
	<div style="max-width: 500px; border: 1px solid #E1E1E8; border-radius: 4px; padding: 5px; background-color: #eee;">
		<label for="email"><strong>Deine E-Mail-Adresse:</strong></label>


		<div class="input-group">
			<span class="input-group-addon">@</span>
			<input id="email" type="email" name="email" value="" autofocus="">
		</div>
		<br><br><br>

		<div id="bestaetigungscode_holder" style="display: none;">
			Es wurde bereits eine E-Mail mit dem Bestätigungscode an diese Adresse geschickt.<br>
			<label for="bestaetigungscode"><strong>Bitte gib den Bestätigungscode an:</strong></label>

			<div>
				<input type="text" name="bestaetigungscode" id="bestaetigungscode" value="" style="width: 280px;">
			</div>
			<br><br><br>
		</div>

		<div id="password_holder">
			<label for="password"><strong>Bitte gib dein Passwort ein:</strong></label>

			<div>
				<input type="password" name="password" id="password" value="" style="width: 280px;">
			</div>
			<br><br><br>
		</div>

		<script>
			$(function () {
				$("#email").on("change blur", function () {
					var val = $("#email").val(),
						$pw = $("#password_holder"),
						$best = $("#bestaetigungscode_holder");
					if (val == "") {
						$pw.show().find("input[type=password]").prop("required", true);
						$best.hide();
					} else {
						$.get("<?php echo CHtml::encode($this->createUrl("index/ajaxEmailIstRegistriert")); ?>", {email: val }, function (ret) {
							if (ret == "-1") {
								$pw.hide();
								$pw.find("input[type=password]").prop("required", false);
								$best.hide();
								$best.find("input[type=text]").prop("required", false);
							} else if (ret == "1") {
								$pw.show();
								$pw.find("input[type=password]").prop("required", true).focus();
								$best.hide();
								$best.find("input[type=text]").prop("required", false);
							} else {
								$pw.hide();
								$pw.find("input[type=password]").prop("required", false);
								$best.show();
								$best.find("input[type=text]").prop("required", true).focus();
							}
							$("#savebutton").prop("disabled", false);
						});
					}
				});
			})
		</script>

		<div style="text-align: center;">
			<button type="submit" name="<?php echo AntiXSS::createToken("login"); ?>" class="btn btn-primary" id="savebutton" disabled>Einloggen</button>
		</div>

	</div>


</form>