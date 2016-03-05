<?php
/**
 * @var AdminController
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
                    <li><?= CHtml::link("BürgerInnenversammlungen", $this->createUrl("admin/buergerInnenversammlungen")) ?></li>
                <? }
                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_USER)) {
                    ?>
                    <li><?= CHtml::link("StadträtInnen: Accounts", $this->createUrl("admin/stadtraetInnenBenutzerInnen")) ?></li>
                <?
                }
                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_TAG)) {
                    ?>
                    <li><?= CHtml::link("Tags", $this->createUrl("admin/tags")) ?></li>
                <?
                }
                ?>
            </ul>
        </div>
    </div>
</section>
