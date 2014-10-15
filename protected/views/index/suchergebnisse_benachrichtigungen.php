<?php

/**
 * @var RISSucheKrits $krits
 * @var string $msg_ok
 * @var string $msg_err
 * @var bool $email_bestaetigt
 * @var bool $email_angegeben
 * @var bool $eingeloggt
 * @var bool $wird_benachrichtigt
 * @var BenutzerIn $ich
 */


$form_state = "";
if ($email_bestaetigt) {
	$form_state = "bestaetigt";
	if ($wird_benachrichtigt) $form_state .= " wird_benachrichtigt";
	else $form_state .= " wird_nicht_benachrichtigt";
} elseif ($email_angegeben) {
	$form_state = "email_angegeben";
} else {
	$form_state = "";
}

$pre_email = ($ich ? CHtml::encode($ich->email) : "");
?>

<button type="submit" class="btn btn-info btn-raised btn-lg"><span class="email">@</span> Über neue Treffer benachrichtigen</button>

<form method="POST" class="modal suchergebnis_titlebox_holder <?= $form_state ?>" action="<?= CHtml::encode($krits->getUrl()) ?>" id="benachrichtigung_einrichten">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title">Benachrichtigung bei neuen Treffern</h4>
			</div>
			<div class="modal-body">


				<div class="email_field">
					<label for="email"><strong>Deine E-Mail-Adresse:</strong></label>

					<input id="email" type="email" name="email" value="<?php echo $pre_email; ?>" class="form-control" placeholder="meine@email-adresse.de">
				</div>
				<br>

				<div class="bestaetigt">
					<div style="text-align: center;" class="wird_benachrichtigt">
						<div class="button_hover_change">
							<button class="btn btn-nohover"><span class="fontello-ok" style="color: green;"></span> Du wirst benachrichtigt</button>
							<button type="button" class="btn btn-primary btn-hover ben_del_button">Nicht mehr benachrichtigen!</button>
						</div>
					</div>
					<div style="text-align: center;" class="wird_nicht_benachrichtigt">
						<div class="button_hover_change">
							<button class="btn btn-nohover">Keine Benachrichtigung aktiv</button>
							<button type="submit" class="btn btn-primary btn-hover">Benachrichtigen!</button>
						</div>
					</div>

				</div>
				<div class="email_angegeben">
					<div id="bestaetigungscode_holder">
						Dir wurde eine E-Mail mit dem Bestätigungscode an diese Adresse geschickt.<br>
						<label for="bestaetigungscode"><strong>Bitte gib den Bestätigungscode an:</strong></label>

						<div>
							<input type="text" name="bestaetigungscode" id="bestaetigungscode" value="">
						</div>
						<br>
					</div>
				</div>
				<div class="nicht_eingeloggt">

					<div id="password_holder">
						Du hast bereits einen Zugang beim Ratsinformant.<br>
						<label for="password"><strong>Bitte gib dein Passwort ein:</strong></label>

						<div>
							<input type="password" name="password" id="password" value="">
						</div>
						<br>
					</div>
				</div>


				<div class="submit_row">
					<button type="submit" name="<?php echo AntiXSS::createToken("anmelden"); ?>" class="btn btn-primary" id="savebutton">Benachrichtigung einrichten</button>
				</div>

				<script>
					$(function () {
						var $holder = $("#benachrichtigung_einrichten"),
							curr_krits = <?=json_encode($krits->getUrlArray())?>;
						$(".ben_del_button").click(function () {
							var params = $.extend(curr_krits, {
								"<?php echo AntiXSS::createToken("benachrichtigung_save"); ?>": 1
							});
							$.post("<?php echo CHtml::encode($this->createUrl("benachrichtigungen/ajaxBenachrichtigungDel")); ?>", params, function (ret) {
								if (ret["status"] == "done") {
									$holder.removeClass("email_angegeben nicht_eingeloggt wird_benachrichtigt").addClass("bestaetigt wird_nicht_benachrichtigt");
								} else {
									alert("Ein Fehler ist aufgetreten");
								}
							});
						});

						$holder.submit(function (ev) {
							ev.preventDefault();
							var email = $("#email").val(),
								$pw = $("#password"),
								$best = $("#bestaetigungscode"),
								params = $.extend(curr_krits, {
									"email": email,
									"password": $pw.val(),
									"bestaetigung": $best.val(),
									"<?php echo AntiXSS::createToken("benachrichtigung_save"); ?>": 1
								});
							$.post("<?php echo CHtml::encode($this->createUrl("benachrichtigungen/ajaxBenachrichtigungAdd")); ?>", params, function (ret) {
								switch (ret["status"]) {
									case "needs_login":
										$holder.removeClass("bestaetigt email_angegeben").addClass("nicht_eingeloggt");
										$holder.find("#password").focus();
										break;
									case "done":
										$holder.removeClass("email_angegeben nicht_eingeloggt wird_nicht_benachrichtigt").addClass("bestaetigt wird_benachrichtigt");
										break;
									case "login_err_pw":
										alert("Ungültiges Passwort");
										break;
									case "login_err_code":
										alert("Ungültiger Code");
										break;
									case "not_confirmed":
										$holder.removeClass("bestaetigt nicht_eingeloggt").addClass("email_angegeben");
										break;
									case "unknown_sent":
										$holder.removeClass("bestaetigt nicht_eingeloggt").addClass("email_angegeben");
										break;
								}
							});
						});

					})
				</script>
			</div>

		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			<button type="button" class="btn btn-primary">Save changes</button>
		</div>
	</div>
</form>

