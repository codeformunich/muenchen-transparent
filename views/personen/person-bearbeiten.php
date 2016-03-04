<?php

use yii\helpers\Html;
use Yii;
use app\components\AntiXSS;

/**
 * @var StadtraetIn $person
 * @var IndexController $this
 */

$this->title = "Bearbeiten: " . $person->getName();

$x = explode("-", $person->geburtstag);
if (count($x) == 3 && $x[1] > 0) {
    $geburtstag = $x[2] . "." . $x[1] . "." . $x[0];
} elseif (count($x) == 3) {
    $geburtstag = $x[2];
} else {
    $geburtstag = "";
}

?>
<section class="well">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= Html::encode(Url::to("index/startseite")) ?>">Startseite</a><br></li>
        <li><a href="<?= Html::encode(Url::to("personen/index")) ?>">Personen</a><br></li>
        <li><a href="<?= Html::encode($person->getLink()) ?>"><?=Html::encode($person->getName())?></a><br></li>
        <li class="active">Bearbeiten</li>
    </ul>

    <h1>Bearbeiten: <?= Html::encode($person->getName()) ?></h1>

    <form method="post" style="max-width: 500px; margin-left: auto; margin-right: auto; margin-top: 40px;">
        <fieldset>
            <div class="form-group row" style="margin-bottom: 30px;">
                <label for="inputEmail" class="col-md-4 control-label">E-Mail (öffentlich):</label>

                <div class="col-md-8">
                    <input type="email" name="email" class="form-control" id="inputEmail" placeholder="E-Mail-Adresse" value="<?= Html::encode($person->email) ?>">
                </div>
            </div>

            <div class="form-group row" style="margin-bottom: 30px;">
                <label for="inputWebsite" class="col-md-4 control-label">Website:</label>

                <div class="col-md-8">
                    <input type="text" name="web" class="form-control" id="inputWebsite" placeholder="https://www.meine-website.de/" value="<?= Html::encode($person->web) ?>">
                </div>
            </div>

            <div class="form-group row" style="margin-bottom: 30px;">
                <label for="inputFacebook" class="col-md-4 control-label">Facebook-Profil/Seite:</label>

                <div class="col-md-8">
                    <input type="text" name="facebook" class="form-control" id="inputFacebook" placeholder="https://www.facebook.com/....."
                           value="<?= Html::encode($person->facebook) ?>">
                </div>
            </div>

            <div class="form-group row" style="margin-bottom: 30px;">
                <label for="inputTwitter" class="col-md-4 control-label">Twitter-Name:</label>

                <div class="col-md-8">
                    <input type="text" name="twitter" class="form-control" id="inputTwitter" placeholder="@BenutzerInnenname" value="<?=Html::encode($person->twitter)?>">
                </div>
            </div>

            <div class="form-group row" style="margin-bottom: 30px;">
                <label for="inputGeburtstag" class="col-md-4 control-label">Geburtstag:</label>

                <div class="col-md-8">
                    <input type="text" name="geburtstag" class="form-control" id="inputGeburtstag" placeholder="TT.MM.JJJJ" value="<?=Html::encode($geburtstag)?>">
                </div>
            </div>

            <div class="form-group row" style="margin-bottom: 30px;">
                <label for="inputBeschreibung" class="col-md-4 control-label">Selbstbeschreibung:</label>

                <div class="col-md-8">
                    <textarea name="beschreibung" id="inputBeschreibung" rows="4" style="width: 100%;"><?=Html::encode($person->beschreibung)?></textarea>
                </div>
            </div>

            <div class="form-group" style="text-align: center;">
                <button type="submit" class="btn btn-primary" name="<?= AntiXSS::createToken("save") ?>">Speichern</button>
            </div>

        </fieldset>
    </form>

</section>
