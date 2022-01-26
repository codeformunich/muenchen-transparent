<?php

/**
 * @var IndexController $this
 * @var Antrag[] $antraege
 * @var string|null $neuere_url_ajax
 * @var string|null $neuere_url_std
 * @var string|null $aeltere_url_ajax
 * @var string|null $aeltere_url_std
 * @var bool $weiter_links_oben
 * @var string $datum
 * @var string $title
 * @var float $geo_lng
 * @var float $geo_lat
 * @var float $radius
 * @var OrtGeo $naechster_ort
 * @var int $zeige_ba_orte
 * @var bool $zeige_jahr
 */

if (isset($title) && $title !== null) {
    $erkl_str = CHtml::encode($title);
} elseif (isset($datum)) {
    if ($datum == date("Y-m-d", time() - 3600 * 24) . " 00:00:00") $erkl_str = "des letzten Tages";
    else {
        $erkl_str = RISTools::datumstring($datum);
        if ($erkl_str > 0) $erkl_str = "vom " . $erkl_str;
        else $erkl_str = "von " . $erkl_str;
    }
    $erkl_str = "Stadtratsdokumente " . $erkl_str;
} elseif (isset($datum_von) && isset($datum_bis)) {
    $erkl_str = "Dokumente vom " . RISTools::datumstring($datum_von) . " bis " . RISTools::datumstring($datum_bis);
} else {
    $radius = round($radius);
    $erkl_str = "Stadtratsdokumente: etwa {$radius}m um \"" . CHtml::encode($naechster_ort->ort) . "\"";
}

if (!isset($zeige_ba_orte)) $zeige_ba_orte = 0;

$datum_nav = ((isset($neuere_url_std) && $neuere_url_std !== null) || (isset($aeltere_url_std) && $aeltere_url_std !== null));


if (count($antraege) > 0) {
    echo '<h2>' . $erkl_str . '</h2><br>';

    if ($weiter_links_oben && $datum_nav) {
        ?>
        <div class="antragsliste_nav">
            <?php
            if (isset($neuere_url_ajax) && $neuere_url_ajax !== null) {
                ?>
                <div class="neuere_caller">
                    <a href="<?= CHtml::encode($neuere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($neuere_url_ajax) ?>');" rel="next"><span
                            class="glyphicon glyphicon-chevron-left"></span> Neuere Dokumente</a>
                </div>
            <?php
            } elseif (isset($neuere_url_std) && $neuere_url_std !== null) {
                ?>
                <div class="neuere_caller">
                    <a href="<?= CHtml::encode($neuere_url_std) ?>" rel="next"><span class="glyphicon glyphicon-chevron-left"></span> Neuere Dokumente</a>
                </div>
            <?php
            }

            if (isset($aeltere_url_ajax) && $aeltere_url_ajax !== null) {
                ?>
                <div class="aeltere_caller">
                    <a href="<?= CHtml::encode($aeltere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($aeltere_url_ajax) ?>');" rel="next">Ältere
                        Dokumente <span class="glyphicon glyphicon-chevron-right"></span></a>
                </div>
            <?php
            } elseif (isset($aeltere_url_std) && $aeltere_url_std !== null) {
                ?>
                <div class="aeltere_caller">
                    <a href="<?= CHtml::encode($aeltere_url_std) ?>" rel="next">Ältere Dokumente <span class="glyphicon glyphicon-chevron-right"></span></a>
                </div>
            <?php
            }
            ?>
        </div>
    <?php
    }
    echo '<ul class="antragsliste2">';
    $akt_datum = null;

    $by_date = array();
    foreach ($antraege as $ant) {
        if (!method_exists($ant, "getName")) {
            echo '<li class="panel panel-danger">
            <div class="panel-heading">Fehler</div><div class="panel-body">' . get_class($ant) . "</div></li>";
        } else {
            $datum = date("Y-m-d", $ant->getDokumentenMaxTS());
            if (!isset($by_date[$datum])) $by_date[$datum] = array();
            $by_date[$datum][] = $ant;
        }
    }
    krsort($by_date);

    foreach ($by_date as $date => $entries) foreach ($entries as $entry) {
        /** @var Antrag $entry */
        $doklist = "";
        foreach ($entry->dokumente as $dokument) {
            $dokurl = $dokument->getLink();
            $doklist .= "<li><a href='" . CHtml::encode($dokurl) . "'";
            if (substr($dokurl, strlen($dokurl) - 3) == "pdf") $doklist .= ' class="pdf"';
            $doklist .= ">" . CHtml::encode($dokument->getName(false)) . "</a></li>";
            $dat = RISTools::date_iso2timestamp($dokument->getDate());
        }

        $titel = $entry->getName(true);
        echo '<li class="panel panel-primary">
        <div class="panel-heading"><a href="' . CHtml::encode($entry->getLink()) . '"';
        if (mb_strlen($titel) > 110) echo ' title="' . CHtml::encode($titel) . '"';
        echo '><span>';
        echo CHtml::encode($titel) . '</span></a></div>';
        echo '<div class="panel-body">';

        $this->renderPartial("/antraege/metainformationen", array(
            "antrag" => $entry,
            "zeige_ba_orte" => $zeige_ba_orte
        ));

        echo "<ul class='dokumente'>";
        echo $doklist;
        echo "</ul></div></li>\n";
    }
    echo '</ul>';
}

if ($datum_nav) {
    ?>
    <div class="antragsliste_nav">
        <?php
        if (isset($neuere_url_ajax) && $neuere_url_ajax !== null) {
            ?>
            <div class="neuere_caller">
                <a href="<?= CHtml::encode($neuere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($neuere_url_ajax) ?>');" rel="next"><span
                        class="glyphicon glyphicon-chevron-left"></span> Neuere Dokumente</a>
            </div>
        <?php
        } elseif (isset($neuere_url_std) && $neuere_url_std !== null) {
            ?>
            <div class="neuere_caller">
                <a href="<?= CHtml::encode($neuere_url_std) ?>" rel="next"><span class="glyphicon glyphicon-chevron-left"></span> Neuere Dokumente</a>
            </div>
        <?php
        }

        if (isset($aeltere_url_ajax) && $aeltere_url_ajax !== null) {
            ?>
            <div class="aeltere_caller">
                <a href="<?= CHtml::encode($aeltere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($aeltere_url_ajax) ?>');" rel="next">Ältere
                    Dokumente
                    <span class="glyphicon glyphicon-chevron-right"></span></a>
            </div>
        <?php
        } else if (isset($aeltere_url_std) && $aeltere_url_std !== null) {
            ?>
            <div class="aeltere_caller">
                <a href="<?= CHtml::encode($aeltere_url_std) ?>" rel="next">Ältere Dokumente <span class="glyphicon glyphicon-chevron-right"></span></a>
            </div>
        <?php
        }
        ?>
    </div>
<?php
}
