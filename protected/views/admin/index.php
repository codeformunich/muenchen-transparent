<?php
/**
 * @var AdminController $this
 */

?>

<h1>Administrationstools</h1>

<div class="well">
	<ul>
		<li><?= CHtml::link("StadträtInnen/Personen verknüpfen", $this->createUrl("admin/stadtraetInnenPersonen")) ?></li>
		<li><?= CHtml::link("StadträtInnen: Social-Media-Daten", $this->createUrl("admin/stadtraetInnenSocialMedia")) ?></li>
	</ul>
</div>