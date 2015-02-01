<?php

/**
* @var IndexController $this
* @var string $current_url
*/

?>

<section class="col-md-4 col-md-offset-4">
    <div class="well">
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
    })
    </script>

    <form class="form-horizontal form-signin" method="POST" action="<?= CHtml::encode($current_url) ?>">
        <fieldset>
            <legend class="form_row">Einloggen</legend>
            <?
            foreach ($_POST as $key => $val) if (!in_array($key, array("email", "bestaetigungscode", "password", "password2", AntiXSS::createToken("login"), AntiXSS::createToken("anlegen")))) {
                echo "<input type='hidden' name='" . CHtml::encode($key) . "' value='" . CHtml::encode($val) . "'>";
            }
            ?>

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

            <button class="btn btn-lg btn-primary btn-block" type="submit" name="<?php echo AntiXSS::createToken("login_anlegen"); ?>"><span class="login">Login</span><span
                class="anlegen">Anlegen</span></button>
            </fieldset>
        </form>
    </div>
</section>
