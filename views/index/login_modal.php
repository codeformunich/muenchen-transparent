<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\AntiXSS;

?>

<section class="modal fade" id="benachrichtigung_login">
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
					<?php echo Html::link("Passwort vergessen?", Url::to("index/resetPasswordForm")) ?>
				</div>

			</fieldset>
			<div class="modal-footer submit_row">
				<button class="btn btn-lg btn-primary btn-block" id="login" type="submit" name="<?php echo AntiXSS::createToken("login_anlegen"); ?>">
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
