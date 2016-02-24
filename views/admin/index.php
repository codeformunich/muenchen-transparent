<?php

use app\models\BenutzerIn;
use yii\helpers\Html;

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
                    <li><?= Html::link("StadträtInnen/Personen verknüpfen", $this->createUrl("admin/stadtraetInnenPersonen")) ?></li>
                    <li><?= Html::link("StadträtInnen: Social-Media-Daten", $this->createUrl("admin/stadtraetInnenSocialMedia")) ?></li>
                    <li><?= Html::link("StadträtInnen: Beschreibungen", $this->createUrl("admin/stadtraetInnenBeschreibungen")) ?></li>
                    <li><?= Html::link("BürgerInnenversammlungen", $this->createUrl("admin/buergerInnenversammlungen")) ?></li>
                <? }
                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_USER)) {
                    ?>
                    <li><?= Html::link("StadträtInnen: Accounts", $this->createUrl("admin/stadtraetInnenBenutzerInnen")) ?></li>
                <?
                }
                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_TAG)) {
                    ?>
                    <li><?= Html::link("Tags", $this->createUrl("admin/tags")) ?></li>
                <?
                }
                ?>
            </ul>
        </div>
    </div>
</section>
