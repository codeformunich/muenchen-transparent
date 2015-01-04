<?php
/**
 * @var AdminController $this
 */

$user = $this->aktuelleBenutzerIn();

?>

<section class="row">
	<div class="col-md-12">

		<div class="well">
			<h1>Administrationstools</h1>
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
	</div>
</section>