<?php
/**
 * @var AntraegeController $this
 * @var StadtraetIn[] $stadtraetInnen,
 * @var Person[] $personen
 * @var string|null $msg_ok
 */



?>

<h1>StadträtInnen/Personen-Verknüpfung</h1>

<?
if (!is_null($msg_ok)) echo '<div class="alert alert-success">' . $msg_ok . '</div>';
?>

<form method="POST" style="overflow: auto;">
	<div style="float: left;">
		<? foreach ($personen as $person) {
			echo "<label ";
			if (!is_null($person->stadtraetIn) || $person->typ == Person::$TYP_FRAKTION) echo "style='color: gray;';";
			echo "><input type='radio' name='person' value='" . $person->id . "'> " . CHtml::encode($person->name);
			if (!is_null($person->stadtraetIn)) echo " ( => " . CHtml::encode($person->stadtraetIn->name) . ")";
			if ($person->typ == Person::$TYP_FRAKTION) echo " (Fraktion)";
			echo "</label><br>\n";
		} ?>
	</div>

	<div style="float: left;">
		<label><input type="checkbox" name="fraktion"> Als Fraktion markieren</label><br><br>
		<? foreach ($stadtraetInnen as $stadtraetIn) {
			echo "<label><input type='radio' name='stadtraetIn' value='" . $stadtraetIn->id . "'>";
			echo CHtml::encode($stadtraetIn->name);
			echo "</label><br>\n";
		} ?>
	</div>

	<div style="position: fixed; bottom: 0; left: 45%;">
		<button type="submit" class="btn btn-primary" name="save">Speichern</button>
	</div>
</form>