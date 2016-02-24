<?php

/**
 * @var IndexController $this
 * @var Bezirksausschuss $ba
 * @var Antrag[] $antraege
 * @var string|null $aeltere_url_ajax
 * @var string|null $aeltere_url_std
 * @var string|null $neuere_url_ajax
 * @var string|null $neuere_url_std
 * @var bool $explizites_datum
 * @var array $geodata
 * @var array $geodata_overflow
 * @var string $datum_von
 * @var string $datum_bis
 * @var array $termine
 * @var array $bvs
 * @var Termin[] $termin_dokumente
 * @var Fraktion $fraktionen
 * @var int $tage_zukunft
 * @var int $tage_vergangenheit
 * @var int $tage_vergangenheit_dokumente
 * @var Gremium[] $gremien
 */

$this->layout = "//layouts/width_wide";

$this->pageTitle = "Bezirksausschuss " . $ba->ba_nr . ", " . $ba->name;

?>


<section class="well">
    <h1><?= Html::encode($ba->name) ?>
        <small>(Bezirksausschuss <?= $ba->ba_nr ?>)</small>
    </h1>

    <?
    $this->renderPartial("/index/map", array(
        "ortsbezugszahlgrenze" => 20,
        "geodata_overflow"     => $geodata_overflow
    ));
    ?>
    <script>
        $(function () {
            var $map = $("#map").AntraegeKarte({
                benachrichtigungen_widget: "benachrichtigung_hinweis",
                benachrichtigungen_widget_zoom: 15,
                outlineBA: <?=$ba->ba_nr?>,
                onSelect: function (latlng, rad, zoom) {
                    if (zoom >= 15) {
                        index_geo_dokumente_load("", latlng.lng, latlng.lat, rad);
                    }
                },
                onInit: function () {
                    $map.AntraegeKarte("setAntraegeData", <?=json_encode($geodata)?>, <?=json_encode($geodata_overflow)?>);
                }
            });
        });
    </script>
</section>


<div class="row <? if ($explizites_datum) echo "nur_dokumente"; ?>" id="listen_holder">
    <div class="col col-md-5" id="stadtratsdokumente_holder">
        <div class="well" style="overflow: auto;">
            <? $this->renderPartial("index_antraege_liste", array(
                "aeltere_url_std"   => $aeltere_url_std,
                "neuere_url_std"    => $neuere_url_std,
                "antraege"          => $antraege,
                "datum_von"         => $datum_von,
                "datum_bis"         => $datum_bis,
                "title"             => ($explizites_datum ? null : "Dokumente der letzten $tage_vergangenheit_dokumente Tage"),
                "weitere_url"       => null,
                "weiter_links_oben" => $explizites_datum,
                "zeige_ba_orte"     => $ba->ba_nr,
            )); ?>
        </div>
    </div>
    <div class="col col-md-4 keine_dokumente">
        <div class="well">
            <?
            if (count($termin_dokumente) > 0) {
                /** @var Dokument[] $dokumente */
                $dokumente = array();
                foreach ($termin_dokumente as $termin) {
                    foreach ($termin->antraegeDokumente as $dokument) {
                        $dokumente[] = $dokument;
                    }
                }
                usort($dokumente, function ($dok1, $dok2) {
                    /** @var Dokument $dok1 */
                    /** @var Dokument $dok2 */
                    $ts1 = RISTools::date_iso2timestamp($dok1->getDate());
                    $ts2 = RISTools::date_iso2timestamp($dok2->getDate());
                    if ($ts1 > $ts2) return -1;
                    if ($ts1 < $ts2) return 1;
                    return 0;
                });
                ?>
                <h3>Protokolle &amp; Tagesordnungen</h3>
                <br>
                <ul class="dokumentenliste_small">
                    <? foreach ($dokumente as $dokument) {
                        $name = str_replace(" (oeff)", "", $dokument->getName(true));
                        $name .= " zur Sitzung am " . date("d.m.Y", RISTools::date_iso2timestamp($dokument->termin->termin));
                        echo '<li>';
                        echo "<div class='metainformationen_antraege'>" . Html::encode($dokument->getDisplayDate()) . "</div>";
                        echo Html::link('<span class="glyphicon glyphicon-file"></span> ' . $name, $dokument->getLinkZumDokument());
                        echo '</li>';
                    } ?>
                </ul>
            <? } ?>

            <br>

            <?
            if (count($bvs) > 0) {
                echo '<h3>BürgerInnenversammlung</h3><br>';
                $this->renderPartial("../termine/termin_liste", array(
                    "termine"     => $bvs,
                    "gremienname" => false,
                ));
                echo '<br>';
            }
            ?>

            <h3>Termine</h3>
            <br>
            <?
            $this->renderPartial("../termine/termin_liste", array(
                "termine"     => $termine,
                "gremienname" => false,
            ));
            ?>
            <div style="text-align: right; font-size: 1.5em;">
                <a href="<?=CHtml::encode($this->createUrl("termine/baTermineAlle", ["ba_nr" => $ba->ba_nr]))?>">
                    Alle Termine <span class="glyphicon glyphicon-chevron-right"></span>
                </a>
            </div>
        </div>
    </div>

    <div class="col col-md-3 keine_dokumente">
        <?
        $statistiken = $ba->getInteressanteStatistik();
        if (count($statistiken) > 0) {
            ?>
        <section class="well">
            <h2>Statistik</h2>
            <dl class="ba_funktionen">
                <? foreach ($statistiken as $statistik) {
                    echo '<dt>' . Html::encode($statistik["name"]) . '</dt>';
                    echo '<dd>' . Html::encode($statistik["wert"]) . '</dd>';
                }
                ?>
                </dl>
                </section>
            <?
        }

        $this->renderPartial("../personen/fraktionen", array(
            "fraktionen" => $fraktionen,
            "title"      => "BA-Mitglieder",
        ));

        $funktionen = $ba->mitgliederMitFunktionen();
        if (count($funktionen) > 0) {
            ?>
            <section class="well">
                <h2>Ämter</h2>
                <dl class="ba_funktionen">
                    <?
                    foreach ($funktionen as $funktion) {
                        if (!$funktion->mitgliedschaftAktiv()) continue;
                        $strIn = $funktion->stadtraetIn;
                        echo '<dt>' . Html::encode($funktion->funktion) . '</dt>';
                        echo '<dd><a href="' . Html::encode($strIn->getLink()) . '">' . Html::encode($strIn->getName()) . '</a></dd>';
                        echo "\n";
                    }
                    ?>
                </dl>
            </section>
        <?
        }

        $this->renderPartial("../personen/ausschuss_mitglieder", array(
            "gremien" => $gremien,
            "title"   => "Unterausschüsse",
        ));
        ?>
    </div>

</div>
