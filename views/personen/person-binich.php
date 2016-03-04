<?php

use yii\helpers\Html;
use yii\helpers\Url;
use Yii;

/**
 * @var StadtraetIn $person
 * @var IndexController $this
 */

$this->title = "Ich bin: " . $person->getName();
$ich             = $this->context->aktuelleBenutzerIn();

?>
<section class="col-md-8 col-md-offset-2">

    <section class="well">
        <ul class="breadcrumb" style="margin-bottom: 5px;">
            <li><a href="<?= Html::encode(Url::to("index/startseite")) ?>">Startseite</a><br></li>
            <li><a href="<?= Html::encode(Url::to("personen/index")) ?>">Personen</a><br></li>
            <li><a href="<?= Html::encode($person->getLink()) ?>"><?= Html::encode($person->getName()) ?></a><br></li>
            <li class="active">Bin ich</li>
        </ul>

        <h1>Ich bin: <?= Html::encode($person->getName()) ?></h1>


        <p>Sie können uns über die vorgefertigte Mail unten anschreiben, dass wir Ihren Zugang mit Ihrem Profil hier auf München Transparent verbinden.</p>

        <p>Dann können Sie zusätzliche Informationen über sich und Ihre politische Arbeit veröffentlichen, Angaben ändern oder auch wieder löschen.</p>

        <p>Da wir keine offizielle Liste der E-Mail-Adressen aller StadträtInnen und BA-Mitglieder haben, prüfen wir die Legitimität der Anfrage anhand der E-Mail-Adresse Ihres
            Accounts (<?= Html::encode($ich->email) ?>). Eventuell halten wir dabei noch kurz Rücksprache, um das zu prüfen.</p>

        <div style="text-align: center;">
            <?
            $email = "mailto:" . Yii::$app->params["adminEmail"] . "?subject=" . rawurlencode("Zuordnung eines BA/StR-Profils") . "&body=";
            $text = "Hallo!\n\nBitte ordnen Sie meinen Account mit der E-Mail-Adresse\n" . $ich->email . "\ndem folgendem Profil auf München Transparent zu:\n" . SITE_BASE_URL . $person->getLink() . "\n\n\n";
            $email .= rawurlencode($text)
            ?>
            <a class="btn btn-primary" href="<?=Html::encode($email)?>">
                <span class="glyphicon" style="font-weight: bold; font-size: 1.4em;">@</span>
                Anfrage stellen
            </a>
        </div>
    </section>


</section>
