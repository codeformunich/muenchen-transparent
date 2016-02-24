<?php

/**
* @var IndexController $this
* @var string $current_url
*/
$this->pageTitle = "Passwort zurücksetzen";
?>


<section class="col-md-4 col-md-offset-4">
    <div class="well">

        <form class="form-horizontal form-signin" method="POST" action="<?= Html::encode($current_url) ?>">
            <fieldset>
                <legend class="form_row">Neues Passwort setzen</legend>

                <div class="form_row">
                    <label for="password" class="control-label sr-only">Passwort</label>
                    <input id="password" name="password" type="password" class="form-control" placeholder="Passwort" required autofocus>
                </div>
                <div class="form_row">
                    <label for="password2" class="control-label sr-only">Passwort bestätigen</label>
                    <input id="password2" name="password2" type="password" class="form-control" placeholder="Passwort bestätigen" required>
                </div>

                <button class="btn btn-lg btn-primary btn-block" type="submit" name="<?php echo AntiXSS::createToken("reset_password"); ?>">Setzen</button>
            </fieldset>
        </form>
    </div>
</section>
