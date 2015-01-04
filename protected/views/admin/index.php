<?php
/**
 * @var AdminController $this
 */

$user = $this->aktuelleBenutzerIn();

?>

<h1>Administrationstools</h1>

<div class="well">
	<ul>
		<?
		if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_CONTENT)) {
			?>
			<li><?= CHtml::link("StadträtInnen/Personen verknüpfen", $this->createUrl("admin/stadtraetInnenPersonen")) ?></li>
			<li><?= CHtml::link("StadträtInnen: Social-Media-Daten", $this->createUrl("admin/stadtraetInnenSocialMedia")) ?></li>
			<li><?= CHtml::link("StadträtInnen: Beschreibungen", $this->createUrl("admin/stadtraetInnenBeschreibungen")) ?></li>
		<? }
		if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_USER)) {
			?>
			<li><?= CHtml::link("StadträtInnen: Accounts", $this->createUrl("admin/stadtraetInnenBenutzerInnen")) ?></li>
		<?
		}
		?>
	</ul>
</div>