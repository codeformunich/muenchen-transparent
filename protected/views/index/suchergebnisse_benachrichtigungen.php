<?php

/**
 * @var string $current_url
 * @var string $msg_ok
 * @var string $msg_err
 * @var bool $email_bestaetigt
 * @var bool $email_angegeben
 * @var bool $eingeloggt
 * @var BenutzerIn $ich
 */


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
<? } ?>


<form method="POST" style=" float: left; width: 300px; margin: 20px;" action="<?=CHtml::encode($current_url)?>">

<?php if ($email_bestaetigt) { ?>
	<div style="max-width: 500px; border: 1px solid #E1E1E8; border-radius: 4px; padding: 5px; background-color: #dadada;">

		Deine E-Mail-Adresse:<br><strong><?php echo CHtml::encode($ich->email); ?></strong> (bestätigt)

	</div>

	<div style="max-width: 500px; border: 1px solid #E1E1E8; border-radius: 4px; padding: 5px; background-color: #eee;">

		<div style="text-align: center;">
			<button type="submit" name="<?php echo AntiXSS::createToken("speichern"); ?>" class="btn btn-primary" id="savebutton">Speichern</button>
		</div>
	</div>
<?php } elseif ($email_angegeben) { ?>
<div style="max-width: 500px; border: 1px solid #E1E1E8; border-radius: 4px; padding: 5px; background-color: #eee;">

	<label for="email"><strong>Deine E-Mail-Adresse:</strong></label>

	<div class="input-group">
		<span class="input-group-addon">@</span>
		<input id="email" type="email" name="email" value="<?php echo CHtml::encode($ich->email); ?>" disabled style="width: 250px;">
		<input type="hidden" name="email" value="<?php echo CHtml::encode($ich->email); ?>">
	</div>
	<br>
	<br>

	<label for="bestaetigungscode">Bitte gib den Bestätigungscode an:</label>

	<div>
		<input type="text" name="bestaetigungscode" id="bestaetigungscode" value="" autofocus style="width: 280px;">
	</div>
	<br>
	<br>

	<button type="submit" name="<?php echo AntiXSS::createToken("anmelden"); ?>" class="btn btn-primary">E-Mail bestätigen</button>
	<?
	} else {
		?>
		<div style="max-width: 500px; border: 1px solid #E1E1E8; border-radius: 4px; padding: 5px; background-color: #eee;">
			<label for="email"><strong>Deine E-Mail-Adresse:</strong></label>


			<div class="input-group">
				<span class="input-group-addon">@</span>
				<input id="email" type="email" name="email" value="<?php if ($ich) echo CHtml::encode($ich->email); ?>">
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

			<div id="password_holder" style="display: none;">
				Du hast bereits einen Zugang bei Antragsgrün.<br>
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
							$pw.hide();
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
				<button type="submit" name="<?php echo AntiXSS::createToken("anmelden"); ?>" class="btn btn-primary" id="savebutton" disabled>Speichern</button>
			</div>

		</div>
	<? } ?>
</div>
</form>