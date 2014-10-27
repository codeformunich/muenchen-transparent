<?
/**
 * @var InfosController $this
 * @var string $current_url
 * @var string $msg_err
 */
$this->pageTitle = "Feedback an uns";

?>

<section class="col-md-6 col-md-offset-3">
	<?
	if ($msg_err != "") {
		?>
		<div class="alert alert-danger">
			<?php echo $msg_err; ?>
		</div>
	<?
	}
	?>
	<div class="well">
		<form class="form-horizontal form-signin" method="POST" action="<?= CHtml::encode($current_url) ?>">
			<fieldset>
				<legend class="form_row">Verbesserungsvorschläge? Fehler gefunden?</legend>

				<div class="checkbox form_row" style="margin-bottom: 10px;">
					<label>
						<input type="checkbox" name="answer_wanted" onchange="$('#email_row').toggle();"> Ist eine Antwort gewünscht?
					</label>
				</div>

				<div class="form_row" id="email_row" style="margin-bottom: 10px; display: none;">
					<label for="email" class="control-label" style="margin-bottom: 5px;">Ihre E-Mail-Adresse</label>
					<input id="email" type="email" name="email" class="form-control" placeholder="meine@email.de">
				</div>

				<label for="message">Ihre Nachricht:</label><br>
				<textarea class="form-control" name="message" rows="7" id="message" autofocus required></textarea>

				<button class="btn btn-lg btn-primary btn-block" type="submit" name="<?php echo AntiXSS::createToken("send"); ?>"><span class="glyphicon glyphicon-envelope"></span> Abschicken</button>
		</form>
	</div>

</section>
