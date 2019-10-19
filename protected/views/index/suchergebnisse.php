<?php
/**
 * @var IndexController $this
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 * @var RISSucheKrits $krits
 * @var array $available_facets
 * @var array $used_facets
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
<section class="well suchoptionen">
    <h1> <?= ($krits->getKritsCount() > 1) ? "Suche" : CHtml::encode($krits->getBeschreibungDerSuche()) ?> </h1>

    <div class="row">

    <?php
    // Anzeigen der Suchkriterien und die Möglichkeit, diese zu entfernen //
    if ($krits->getKritsCount() > 1) { ?>
    <div class="suchkrits_interaktiv col-md-10">
        <h4>Gefunden wurden Dokumente mit den folgenden Kriterien:</h4>
        <ul>
            <?php foreach ($krits->krits as $krit) {
                $single_krit = new RISSucheKrits([$krit]);
                $one_removed = new RISSucheKrits();
                foreach ($krits->krits as $krit2) {
                    if ($krit2 != $krit)
                        $one_removed->krits[] = $krit2;
                } ?>
                <li>
                    <span class="suchkrits_beschreibung"><?= $single_krit->getBeschreibungDerSuche() ?></span>

                    <?php // bearbeiten ?>
                    <?php foreach ($used_facets as $facets) {
                        if ($facets["typ"] == $krit["typ"]) {
                            ?>
                            <div class="dropdown">
                                <button data-toggle="dropdown" title="Einen anderen Wert für dieses Kriterium wählen">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php foreach ($facets["group"] as $facet) { ?>
                                        <li>
                                            <a href="<?= $facet['url'] ?>"><?= $facet['name'] . ' (' . $facet['count'] . ')' ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <?php
                        }
                    } ?>

                    <?php // Einzeln suchen ?>
                    <a href="<?= $single_krit->getUrl() ?>"
                       title='Nach "<?= $single_krit->getBeschreibungDerSuche() ?>" suchen'>
                        <span class="fontello fontello-search"></span>
                    </a>

                    <?php // Entfernen ?>
                    <a href="<?= $one_removed->getUrl() ?>"
                       title='Kriterium "<?= $single_krit->getBeschreibungDerSuche() ?>" enfernen'>
                        <span class="fontello fontello-cancel"></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
    </div>

    <div class="row">

    <?php
    // Möglichkeiten, die Suche weiter einzuschränken //
    $has_facets = false;
    foreach ($available_facets as $facets) if (count($facets["group"]) > 1) {
        $has_facets = true;
    }

    if ($has_facets) { ?>
    <section class="suchergebnis_eingrenzen col-md-10">
        <ul>
            <?php foreach ($available_facets as $facets) { ?>
                <li class="dropdown">
                    <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                        <?= CHtml::encode($facets["name"]) ?> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <?php foreach ($facets["group"] as $facet) { ?>
                            <li>
                                <a href="<?= $facet['url'] ?>"><?= $facet['name'] . ' (' . $facet['count'] . ')' ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>


    <?php // Buttons mit Extras // ?>
    <div class="col-md-10">
        <?php
        $this->renderPartial("suchergebnisse_benachrichtigungen", array(
            "eingeloggt" => $eingeloggt,
            "email_angegeben" => $email_angegeben,
            "email_bestaetigt" => $email_bestaetigt,
            "wird_benachrichtigt" => $wird_benachrichtigt,
            "ich" => $ich,
            "krits" => $krits
        ));
        ?>
        <a href="<?= CHtml::encode($krits->getFeedUrl()) ?>">
            <button type="button" name="<?= AntiXSS::createToken("benachrichtigung_add") ?>"
                    class="btn btn-default benachrichtigung_std_button">
                <?php /* class=glyphicon, damit die css-Styles die gleichen wie bei dem Benachrichtigungs-Button sind */ ?>
                <span class="glyphicon fontello-rss"></span> Suchergebnisse als RSS-Feed
            </button>
        </a>
    </div>

    <?php // Needed to keep the map from floating right ?>
    </div>
    <div class="row">

    <?php
    // Bei Geokriterien wird eine Karte angezeigt //
    if (!is_null($geodata) && count($geodata) > 0) {
        $this->load_leaflet = true;
        $geokrit = $krits->getGeoKrit();
        ?>
        <div id="mapholder">
            <div id="map"></div>
        </div>
        <div id="overflow_hinweis" <?php if (count($geodata_overflow) == 0) echo "style='display: none;'"; ?>>
            <label><input type="checkbox" name="zeige_overflow">
                Zeige <span class="anzahl">
                    <?= (count($geodata_overflow) == 1 ? "1 Dokument" : count($geodata_overflow) . " Dokumente") ?>
                </span> mit über 20 Ortsbezügen
            </label>
        </div>

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
    <?php } ?>

    </div>

</section>

<section class="well suchergebnisse">
    <h1>Suchergebnisse</h1>
    <p>Zeige <?= count($ergebnisse->getDocuments()) ?> von <?= $ergebnisse->getNumFound() ?> Dokumenten</p>
    <div style="height: 15px;"></div>

    <?php
    if ($krits->getKritsCount() > 0) $this->renderPartial("../benachrichtigungen/suchergebnisse_liste", array(
        "ergebnisse" => $ergebnisse,
    ));
    ?>
</section>
