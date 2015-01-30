<?
/**
 * @var InfosController $this
 * @var string $current_url
 * @var string $this->msg_err
 */
$this->pageTitle = "Feedback an uns";

if (Yii::app()->user)
    $email_default = 'value = "' . Yii::app()->user->id . '"';
else
    $email_default = 'placeholder="meine@email.de"';
?>

<section class="col-md-6 col-md-offset-3">
    <?
    if ($this->msg_err != "") {
        ?>
        <div class="alert alert-danger">
            <?php echo $this->msg_err; ?>
        </div>
    <?
    }
    ?>
    <div class="well">
        <form class="form-horizontal form-signin" method="POST" action="<?= CHtml::encode($current_url) ?>" id="feedback_form">
            <fieldset>
                <legend class="form_row">Verbesserungsvorschläge? Fehler gefunden?</legend>

                <div class="checkbox form_row" style="margin-bottom: 10px;">
                    <label>
                        <input type="checkbox" name="answer_wanted"> Ist eine Antwort gewünscht?
                    </label>
                </div>

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
        <script>
            $(function () {
                var $form = $("#feedback_form");
                $form.find("input[name=answer_wanted]").change(function () {
                    if ($(this).prop("checked")) {
                        $("#email_row").show();
                        $("#email").prop("required", true);
                    } else {
                        $("#email_row").hide();
                        $("#email").prop("required", false);
                    }
                }).trigger("change");
            });
        </script>
    </div>

</section>
