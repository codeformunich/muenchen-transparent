<?php

use app\models\BenutzerIn;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var AdminController $this
 */

$user = $this->context->aktuelleBenutzerIn();

?>

<section class="row">
    <div class="col-md-12">

        <div class="well">
            <h1>Administrationstools</h1>
            <ul>
                <?
                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_CONTENT)) {
                    ?>
                    <li><?= Html::a("StadträtInnen/Personen verknüpfen", Url::to("admin/stadtraetInnenPersonen")) ?></li>
                    <li><?= Html::a("StadträtInnen: Social-Media-Daten", Url::to("admin/stadtraetInnenSocialMedia")) ?></li>
                    <li><?= Html::a("StadträtInnen: Beschreibungen", Url::to("admin/stadtraetInnenBeschreibungen")) ?></li>
                    <li><?= Html::a("BürgerInnenversammlungen", Url::to("admin/buergerInnenversammlungen")) ?></li>
                <? }
                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_USER)) {
                    ?>
                    <li><?= Html::a("StadträtInnen: Accounts", Url::to("admin/stadtraetInnenBenutzerInnen")) ?></li>
                <?
                }
                if ($user->hatBerechtigung(BenutzerIn::$BERECHTIGUNG_TAG)) {
                    ?>
                    <li><?= Html::a("Tags", Url::to("admin/tags")) ?></li>
                <?
                }
                ?>
            </ul>
        </div>
    </div>
</section>
