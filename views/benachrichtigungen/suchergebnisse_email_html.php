<?php

use Yii;
use yii\helpers\Html;
use app\components\RISTools;

/**
 * @var BenutzerIn $benutzerIn
 * @var array $data
 */
$css = file_get_contents(Yii::$app->getBasePath() . "/../web/css/build/mail.css") . "\n\n";
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Neue Dokumente im Münchner RIS</title>
    <style><?= $css; ?></style>
</head>

<body>

<div class="container row center-block">
    <div class="col-lg-10 col-lg-offset-1 col-md-12">
        <div class="well suchergebnisse" id="content">
            <h1>Hallo,</h1>
            seit der letzten E-Mail-Benachrichtigung wurde entsprechend deiner Benachrichtigungseinstellungen Folgendes neu gefunden:<br><br>
            <?
            if (count($data["vorgaenge"]) > 0) {
                ?>
                <section class="fullsize">
                    <h3>Abonnierte Vorgänge / Anträge</h3>
                    <ul class="antragsliste">
                        <?
                        foreach ($data["vorgaenge"] as $vorgang) {
                            echo "<li class='listitem'><div class='antraglink'>" . Html::encode($vorgang["vorgang"]) . "</div>";
                            echo "<ul class='dokumente'>";
                            foreach ($vorgang["neues"] as $item) {
                                /** @var IRISItem $item */
                                echo '<li><a href="' . Html::encode($item->getLink()) . '">' . Html::encode($item->getName(true)) . '</a> (' . Html::encode($item->getTypName()) . ')</li><br>';
                            }
                            echo "</ul>";
                            echo "</li>\n";
                        }
                        ?>
                    </ul>
                </section>
            <?
            }

            if (count($data["antraege"]) > 0) {
                ?>
                <h3>Anträge &amp; Vorlagen</h3>
                <ul class="list-group">
                    <?
                    foreach ($data["antraege"] as $dat) {
                        /** @var Antrag $antrag */
                        $antrag         = $dat["antrag"];
                        $dokumente_strs = array();
                        $queries        = array();
                        $max_date       = 0;
                        $doklist        = "";
                        foreach ($dat["dokumente"] as $dok) {
                            /** @var Dokument $dokument */
                            $dokument = $dok["dokument"];
                            $dokurl   = $dokument->getLinkZumDokument();
                            $doklist .= '<a href="' . Html::encode($dokurl) . '" class="dokument"><span class="fontello-download"></span> ' . Html::encode($dokument->name) . '</a><br>';
                            $dat = RISTools::date_iso2timestamp($dokument->getDate());
                            if ($dat > $max_date) $max_date = $dat;

                            foreach ($dok["queries"] as $qu) {
                                /** @var RISSucheKrits $qu */
                                $name = $qu->getTitle($dokument);
                                if (!in_array($name, $queries)) $queries[] = $name;
                            }
                        }
                        ?>
                        <li class='list-group-item'>
                            <div class="row-action-primary">
                                <i class="glyphicon glyphicon-file"></i>
                            </div>
                            <div class="row-content">
                                <h4 class="list-group-item-heading">
                                    <a href="<?= Html::encode($antrag->getLink()) ?>" title="<?= Html::encode($antrag->getName()) ?>" class="overflow-fadeout-white"><span>
                                            <span class="least-content"><?= Html::encode(date("d.m.Y", $max_date)) ?></span>
                                            <?= Html::encode($antrag->getName(true)) ?>
                            </span></a>
                                </h4>

                                <p class="list-group-item-text">
                                    <?
                                    if ($antrag->ba_nr > 0) echo " <span title='" . Html::encode("Bezirksausschuss " . $antrag->ba_nr . " (" . $antrag->ba->name . ")") . "' class='ba'>BA " . $antrag->ba_nr . "</span>, ";
                                    echo $doklist;

                                    echo "<div class='gefunden_ueber'>";
                                    if (count($queries) == 1) {
                                        echo $queries[0];
                                    } else {
                                        echo implode("\"<br>\"", $queries);
                                    }
                                    echo "</div>";
                                    ?>
                                    <span class="border">&nbsp;</span>
                                </p>
                            </div>
                        </li>
                    <?
                    }

                    ?>
                </ul>
            <?
            }
            unset($antrag);

            if (count($data["termine"]) > 0) {
                ?>
                <section class="fullsize">
                    <h3>Sitzungen</h3>
                    <ul class="antragsliste">
                        <?
                        foreach ($data["termine"] as $dat) {
                            /** @var Termin $termin */
                            $termin = $dat["termin"];

                            echo "<li class='listitem'><div class='antraglink'><a href='" . Html::encode($termin->getLink()) . "' title='" . Html::encode($termin->getName()) . "'>";
                            echo Html::encode($termin->getName()) . "</a></div>";

                            $dokumente_strs = array();
                            $queries        = array();
                            $max_date       = 0;
                            $doklist        = "";
                            foreach ($dat["dokumente"] as $dok) {
                                /** @var Dokument $dokument */
                                $dokument = $dok["dokument"];
                                $dokurl   = $dokument->getLinkZumDokument();
                                $doklist .= "<li><a href='" . Html::encode($dokurl) . "'";
                                if (substr($dokurl, strlen($dokurl) - 3) == "pdf") $doklist .= ' class="pdf"';
                                $doklist .= ">" . Html::encode($dokument->name) . "</a></li>";
                                $dat = RISTools::date_iso2timestamp($dokument->getDate());
                                if ($dat > $max_date) $max_date = $dat;

                                foreach ($dok["queries"] as $qu) {
                                    /** @var RISSucheKrits $qu */
                                    $name = $qu->getTitle($dokument);
                                    if (!in_array($name, $queries)) $queries[] = $name;
                                }
                            }

                            echo "<ul class='dokumente'>";
                            echo $doklist;
                            echo "</ul>";

                            echo "<div class='gefunden_ueber'>";
                            if (count($queries) == 1) {
                                echo "Gefunden über: \"" . $queries[0] . "\"";
                            } else {
                                echo "Gefunden über: \"" . implode("\"<br>\"", $queries) . "\"";
                            }
                            echo "</div>";
                            echo "</li>\n";
                        }

                        ?></ul>
                </section>
            <? } ?>
            <br>

            Liebe Grüße,<br>
            &nbsp;
            Das München Transparent-Team
            <br><br>
            <? $url = Url::to("benachrichtigungen/index", array("code" => $benutzerIn->getBenachrichtigungAbmeldenCode())); ?>
            PS: Falls du diese Benachrichtigung nicht mehr erhalten willst, kannst du sie <a href="<?php echo Html::encode($url); ?>">hier abbestellen</a>.

        </div>
    </div>
</div>

</body>
</html>
