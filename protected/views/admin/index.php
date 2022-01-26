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
                <?php
                if ($user->hatBerechtigung(BenutzerIn::BERECHTIGUNG_CONTENT)) {
                    ?>
                    <li><?= CHtml::link("Stadtratsmitglieder/Personen verknüpfen", $this->createUrl("admin/stadtraetInnenPersonen")) ?></li>
                    <li><?= CHtml::link("Stadtratsmitglieder: Social-Media-Daten", $this->createUrl("admin/stadtraetInnenSocialMedia")) ?></li>
                    <li><?= CHtml::link("Stadtratsmitglieder: Beschreibungen", $this->createUrl("admin/stadtraetInnenBeschreibungen")) ?></li>
                    <li><?= CHtml::link("Bürger*innenversammlungen", $this->createUrl("admin/buergerInnenversammlungen")) ?></li>
                <?php }
                if ($user->hatBerechtigung(BenutzerIn::BERECHTIGUNG_USER)) {
                    ?>
                    <li><?= CHtml::link("Stadtratsmitglieder: Accounts", $this->createUrl("admin/stadtraetInnenBenutzerInnen")) ?></li>
                <?php
                }
                if ($user->hatBerechtigung(BenutzerIn::BERECHTIGUNG_TAG)) {
                    ?>
                    <li><?= CHtml::link("Tags", $this->createUrl("admin/tags")) ?></li>
                <?php
                }
                ?>
            </ul>
        </div>
    </div>
</section>
