<?php
/**
 * @var IndexController $this
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 * @var RISSucheKrits $krits
 * @var bool $email_bestaetigt
 * @var bool $email_angegeben
 * @var bool $eingeloggt
 * @var bool $wird_benachrichtigt
 * @var BenutzerIn $ich
 * @var null|array $geodata
 * @var null|array $geodata_overflow
 */

$this->pageTitle = "Suchergebnisse";

?>
<section class="well suchergebnisse">
    <div class="pull-right">
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
        <a href="<?= CHtml::encode($krits->getFeedUrl()) ?>">
        <button type="button" name="<?= AntiXSS::createToken("benachrichtigung_add") ?>" class="btn btn-info btn-raised benachrichtigung_std_button">
            <span class="glyphicon <? /* class=glyphicon, damit die css-Styles die gleichen wie bei dem Benachrichtigungs-Button sind */ ?> fontello-rss"></span>  Suchergebnisse als RSS-Feed
        </button>
        </a>
    </div>

    <?
    if ($krits->getKritsCount() == 1) {
        echo '<h1>' . CHtml::encode($krits->getBeschreibungDerSuche()) . '</h1>';
    } else {
        echo '<h1>Suchergebnisse</h1>';
        echo '<div class="suchkrit_beschreibung">';
        echo CHtml::encode($krits->getBeschreibungDerSuche());
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
                    class="anzahl"><?= (count($geodata_overflow) == 1 ? "1 Dokument" : count($geodata_overflow) . " Dokumente") ?></span> mit über 20 Ortsbezügen</label></div>

        <script>
            $(function () {
                var $map = $("#map").AntraegeKarte({
                    lat: <?=$geokrit["lat"]?>,
                    lng: <?=$geokrit["lng"]?>,
                    size: 14,
                    antraege_data: <?=json_encode($geodata)?>,
                    antraege_data_overflow: <?=json_encode($geodata_overflow)?>,
                });
            });
        </script>
    <?
    }

    // Möglichkeiten, die Suche weiter einzuschränken
    $facet_groups = array();

    $options = [
        ['antrag_typ', 'antrag_typ', 'Dokumenttypen'],
        ['antrag_wahlperiode', 'antrag_wahlperiode', 'Wahlperiode'],
        ['dokument_bas', 'ba', 'BAs']
    ];

    $out = [];

    foreach ($options as $option) {
        // Gewählte Optionen ausschließen
        if ($krits->hasKrit($option[1]))
            continue;

        $single_facet = [];
        $facet = $ergebnisse->getFacetSet()->getFacet($option[0]);

        foreach ($facet as $value => $count) if ($count > 0) {
            if (in_array($value, array("", "?"))) continue;

            $str = [];
            $str['url'] = RISTools::bracketEscape(CHtml::encode($krits->cloneKrits()->addKrit($option[1], $value)->getUrl()));
            $str['count'] = $count;

            if ($option[0] == 'antrag_typ') {
                if (isset(Antrag::$TYPEN_ALLE[$value])) $str['name'] = explode("|", Antrag::$TYPEN_ALLE[$value])[1];
                else if ($value == "stadtrat_termin") $str['name'] = 'Stadtrats-Termin';
                else if ($value == "ba_termin") $str['name'] = 'BA-Termin';
                else $str['name'] = $value;
            } else if ($option[0] == 'antrag_wahlperiode' || $option[0] == 'dokument_bas') {
                $str['name'] = $value;
            }

            $single_facet[] = $str;
        }

        if (count($single_facet) > 0) $facet_groups[$option[2]] = $single_facet;
    }

    // Dropdown, um die Suchergebnisse anzuzeigen
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
                    foreach($facets as $facet) {
                        echo "<li><a href='" . $facet['url'] . "'>";
                        echo $facet['name'] . ' (' . $facet['count'] . ')';
                        echo "</a></li>";
                    }
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
