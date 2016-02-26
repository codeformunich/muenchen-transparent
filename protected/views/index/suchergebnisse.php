<?php
/**
 * @var IndexController
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 * @var RISSucheKrits                            $krits
 * @var bool                                     $email_bestaetigt
 * @var bool                                     $email_angegeben
 * @var bool                                     $eingeloggt
 * @var bool                                     $wird_benachrichtigt
 * @var BenutzerIn                               $ich
 * @var null|array                               $geodata
 * @var null|array                               $geodata_overflow
 */
$this->pageTitle = "Suchergebnisse";

?>
<section class="well suchergebnisse">
    <div class="pull-right" style="text-align: center;">
        <?
        $this->renderPartial("suchergebnisse_benachrichtigungen", array(
            "eingeloggt"          => $eingeloggt,
            "email_angegeben"     => $email_angegeben,
            "email_bestaetigt"    => $email_bestaetigt,
            "wird_benachrichtigt" => $wird_benachrichtigt,
            "ich"                 => $ich,
            "krits"               => $krits
        ));
        ?>
        <div style="font-size: 90%;">
            <a href="<?= CHtml::encode($krits->getFeedUrl()) ?>"><span class="fontello-rss"></span> Suchergebnisse als RSS-Feed</a>
        </div>
    </div>

    <?
    if ($krits->getKritsCount() == 1) {
        echo '<h1>' . CHtml::encode($krits->getTitle()) . '</h1>';
    } else {
        echo '<h1>Suchergebnisse</h1>';
        echo '<div class="suchkrit_beschreibung">';
        echo CHtml::encode($krits->getTitle());
        echo '</div>';
    }
    echo '<br style="clear: both;">';

    if (!is_null($geodata) && count($geodata) > 0) {
        $geokrit     = $krits->getGeoKrit();
        ?>
        <div id="mapholder">
            <div id="map"></div>
        </div>
        <div id="overflow_hinweis" <? if (count($geodata_overflow) == 0) echo "style='display: none;'"; ?>><label><input type="checkbox" name="zeige_overflow"> Zeige <span
                    class="anzahl"><?= (count($geodata_overflow) == 1 ? "1 Dokument" : count($geodata_overflow)." Dokumente") ?></span> mit über 20 Ortsbezügen</label></div>

        <script>
            $(function () {
                var $map = $("#map").AntraegeKarte({
                    lat: <?=$geokrit["lat"]?>,
                    lng: <?=$geokrit["lng"]?>,
                    size: 14,
                    onInit: function () {
                        $map.AntraegeKarte("setAntraegeData", <?=json_encode($geodata)?>, <?=json_encode($geodata_overflow)?>);
                    }
                });
            });
        </script>
    <?
    }

    $facet_groups = array();

    $antrag_typ = array();
    $facet      = $ergebnisse->getFacetSet()->getFacet('antrag_typ');
    foreach ($facet as $value => $count) if ($count > 0) {
        $str = "<li><a href='" . RISTools::bracketEscape(CHtml::encode($krits->cloneKrits()->addAntragTypKrit($value)->getUrl())) . "'>";
        if (isset(Antrag::$TYPEN_ALLE[$value])) {
            $x = explode("|", Antrag::$TYPEN_ALLE[$value]);
            $str .= $x[1] . ' (' . $count . ')';
        } elseif ($value == "stadtrat_termin") $str .= 'Stadtrats-Termin (' . $count . ')';
        elseif ($value == "ba_termin") $str .= 'BA-Termin (' . $count . ')';
        else $str .= $value . " (" . $count . ")";
        $str .= "</a></li>";
        $antrag_typ[] = $str;
    }
    if (count($antrag_typ) > 0) $facet_groups["Dokumenttypen"] = $antrag_typ;

    $wahlperiode = array();
    $facet       = $ergebnisse->getFacetSet()->getFacet('antrag_wahlperiode');
    foreach ($facet as $value => $count) if ($count > 0) {
        if (in_array($value, array("", "?"))) continue;
        $str = "<li><a href='" . RISTools::bracketEscape(CHtml::encode($krits->cloneKrits()->addWahlperiodeKrit($value)->getUrl())) . "'>";
        $str .= $value . ' (' . $count . ')';
        $str .= "</a></li>";
        $wahlperiode[] = $str;
    }
    if (count($wahlperiode) > 0) $facet_groups["Wahlperiode"] = $wahlperiode;

    $has_facets = false;
    foreach ($facet_groups as $name => $facets) if (count($facets) > 1) $has_facets = true;
    if ($has_facets) {
        ?>
        <section class="suchergebnis_eingrenzen">
            <div class="opener"><a href="#suchergebnis_eingrenzen_holder" onclick="$(this).parents('.suchergebnis_eingrenzen').toggleClass('visible'); return false;">
                    <span class="glyphicon glyphicon-chevron-down open_icon"></span>
                    <span class="glyphicon glyphicon-chevron-up close_icon"></span>
                    Suchergebnisse einschränken
                </a></div>
            <div id="suchergebnis_eingrenzen_holder">
                <?
                foreach ($facet_groups as $name => $facets) if (count($facets) > 1) {
                    echo '<div class="eingrenzen_row"><h3>' . CHtml::encode($name) . '</h3><ul>';
                    echo implode("", $facets);
                    echo '</ul></div>';
                }
                ?>
            </div>
        </section>
    <? }

    echo '<br style="clear: both;">';

    if ($krits->getKritsCount() > 0) $this->renderPartial("../benachrichtigungen/suchergebnisse_liste", array(
        "ergebnisse" => $ergebnisse,
    ));
    ?>
</section>
