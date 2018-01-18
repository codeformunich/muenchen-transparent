<?php
/**
 * @var InfosController $this
 * @var string $current_url
 */
$this->pageTitle = "Feedback an uns";

if (Yii::app()->user)
    $email_default = 'value = "' . Yii::app()->user->id . '"';
else
    $email_default = 'placeholder="meine@email.de"';
?>

<section class="col-md-6 col-md-offset-3">
    <div class="well">
        <form class="form-horizontal form-signin" method="POST" action="<?= CHtml::encode($current_url) ?>" id="feedback_form">
            <fieldset>
                <legend class="form_row">Verbesserungsvorschläge? Fehler gefunden?</legend>

                <p>Dieses Formular ist für Feedback zu dieser Website gedacht. Bei Fragen und Kommentaren zum Inhalt der Dokumente wenden sie sich bitte an die zuständigen Stellen der Stadt München.</p>

                <div class="form_row" id="email_row" style="margin-bottom: 10px;">
                    <label for="email" class="control-label" style="margin-bottom: 5px;">Ihre E-Mail-Adresse</label>
                    <input id="email" type="email" name="email" class="form-control"  <?= $email_default ?> >
                </div>

                <label for="message">Ihre Nachricht:</label><br>
                <textarea class="form-control" name="message" rows="7" id="message" autofocus required></textarea>

                <button class="btn btn-lg btn-primary btn-block" type="submit" name="<?php echo AntiXSS::createToken("send"); ?>"><span class="glyphicon glyphicon-envelope"></span>
                    Abschicken
                </button>
        </form>
    </div>

</section>
