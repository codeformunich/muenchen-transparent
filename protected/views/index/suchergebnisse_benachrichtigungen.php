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

?>
<form method="POST" action="<?= CHtml::encode(Yii::app()->createUrl("index/suche")) ?>" id="login_ben_form">
	<?
	$krits = $krits->getUrlArray();
	for ($i = 0; $i < count($krits["krit_typ"]); $i++) {
		echo '<input type="hidden" name="krit_typ[]" value="' . CHtml::encode($krits["krit_typ"][$i]) . '">' . "\n";
		echo '<input type="hidden" name="krit_val[]" value="' . CHtml::encode($krits["krit_val"][$i]) . '">' . "\n";
	}

	if ($eingeloggt) {
	if ($wird_benachrichtigt) {
		?>
		<div class="button_hover_change">
			<button type="submit" name="<?= AntiXSS::createToken("benachrichtigung_del") ?>" class="btn btn-success btn-nohover" style="width: 250px;">
				<span class="glyphicon glyphicon-ok"></span> Du wirst benachrichtigt
			</button>
			<button type="submit" name="<?= AntiXSS::createToken("benachrichtigung_del") ?>" class="btn btn-primary btn-hover ben_del_button" style="width: 250px;">
				Nicht mehr benachrichtigen!
			</button>
		</div>
	<?
	} else {
		?>
		<button type="submit" name="<?= AntiXSS::createToken("benachrichtigung_add") ?>" class="btn btn-info btn-raised ben_std_button">
			<span class="glyphicon">@</span> Über neue Treffer benachrichtigen
		</button>
	<?
	}
	} else {
	?>
		<button type="button" class="btn btn-info btn-raised ben_std_button" data-toggle="modal" data-target="#benachrichtigung_login">
			<span class="glyphicon">@</span> Über neue Treffer benachrichtigen
		</button>
	<input type="hidden" name="<?= AntiXSS::createToken("benachrichtigung_add") ?>" value="1">

		<section class="modal" id="benachrichtigung_login">
			<div class="modal-dialog modal-sm">
				<div class="modal-content form-horizontal form-signin">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Einloggen</h4>
					</div>
					<fieldset class="modal-body">
						<div class="checkbox form_row">
							<label>
								<input type="checkbox" name="register" id="create_account"> Neuen Zugang anlegen
							</label>
						</div>

						<div class="form_row">
							<label for="email" class="control-label sr-only">E-Mail-Adresse</label>
							<input id="email" type="email" name="email" class="form-control" placeholder="Email-Adresse" autofocus required>
						</div>
						<div class="form_row">
							<label for="password" class="control-label sr-only">Passwort</label>
							<input id="password" name="password" type="password" class="form-control" placeholder="Passwort" required>
						</div>
						<div class="form_row">
							<label for="password2" class="control-label sr-only">Passwort bestätigen</label>
							<input id="password2" name="password2" type="password" class="form-control" placeholder="Passwort bestätigen">
						</div>
						<div style="text-align: right; font-style: italic; margin-top: -5px; font-size: 11px; margin-bottom: 7px;">
							<?php echo CHtml::link("Passwort vergessen?", $this->createUrl("index/resetPasswordForm")) ?>
						</div>

					</fieldset>
					<div class="modal-footer submit_row">
						<button class="btn btn-lg btn-primary btn-block" type="submit" name="<?php echo AntiXSS::createToken("login_anlegen"); ?>">
							<span class="login">Login</span><span class="anlegen">Anlegen</span>
						</button>
					</div>
				</div>
			</div>
		</section>
		<script>
			$(function () {
				var $form = $(".form-signin");
				$("#create_account").on("click change", function () {
					if ($(this).prop("checked")) {
						$form.removeClass("login").addClass("anlegen");
					} else {
						$form.removeClass("anlegen").addClass("login");
					}
				}).trigger("change");

			});
		</script>

	<?
	}
	?>
</form>
