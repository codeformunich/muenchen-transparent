<?php

use app\components\AntiXSS;

/**
 * @var IndexController $this
 */

?>
<section class="col-md-4 col-md-offset-4">
  <div class="well">
    <form class="form-horizontal form-signin" method="POST" action="<?= $this->createUrl("benachrichtigungen/PasswortZuruecksetzen") ?>">
      <fieldset>
        <legend class="form_row">Passwort zurücksetzen</legend>

        <div class="form_row">
          <label for="email" class="control-label sr-only">E-Mail-Adresse</label>
          <input id="email" type="email" name="email" class="form-control" placeholder="Email-Adresse" autofocus required>
        </div>

        <button class="btn btn-lg btn-primary btn-block" type="submit" name="<?php echo AntiXSS::createToken("reset_password"); ?>">Zurücksetzen</button>
      </fieldset>
    </form>
  </div>
</section>
