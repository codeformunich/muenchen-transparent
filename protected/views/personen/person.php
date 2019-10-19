<?php
/**
 * @var StadtraetIn $person
 * @var IndexController $this
 */

$this->pageTitle = $person->getName();
$this->html_itemprop = "http://schema.org/Person";

?>
<section class="well">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("personen/index")) ?>">Personen</a><br></li>
        <li class="active"><?= CHtml::encode($person->getName()) ?></li>
    </ul>

    <div style="float: right;"><?php
        echo CHtml::link("<span class='fontello-right-open'></span>Original-Seite im RIS", $person->getSourceLink());
        ?></div>
    <h1 itemprop="name"><?= CHtml::encode($person->getName()) ?></h1>
</section>

<div class="row personentable">
    <div class="col-md-8">
        <section class="well">
            <table class="table">
                <tbody>
                <tr>
                    <th>Fraktion(en):</th>
                    <td>
                        <ul class="mitgliedschaften">
                            <?php
                            $mitgliedschaften = $person->getFraktionsMitgliedschaften();
                            foreach ($mitgliedschaften as $frakts) {
                                echo "<li class='" . ($frakts->mitgliedschaftAktiv() ? 'aktiv' : 'inaktiv') . "'>" . CHtml::encode($frakts->fraktion->getName());
                                if ($frakts->fraktion->ba_nr > 0) {
                                    echo ", Bezirksausschuss " . $frakts->fraktion->ba_nr . " (" . CHtml::encode($frakts->fraktion->bezirksausschuss->name) . ")";
                                    // @Wird noch nicht zuverlässig erkannt; siehe https://github.com/codeformunich/muenchen-transparent/issues/38
                                }
                                if ($frakts->datum_von > 0 && $frakts->datum_bis > 0) {
                                    echo "<br><small>(von " . RISTools::datumstring($frakts->datum_von);
                                    echo " bis " . RISTools::datumstring($frakts->datum_bis) . ")</small>";
                                } elseif ($frakts->datum_von > 0) {
                                    echo "<br>(seit " . RISTools::datumstring($frakts->datum_von) . ")";
                                }
                                echo "</li>";
                            } ?>
                        </ul>
                    </td>
                </tr>
                <?php
                if (count($person->mitgliedschaften) > 0) {
                    ?>
                    <tr>
                        <th>Mitgliedschaften:</th>
                        <td>
                            <ul class="mitgliedschaften">
                                <?php
                                foreach ($person->mitgliedschaften as $mitgliedschaft) {
                                    $gremium = $mitgliedschaft->gremium;
                                    echo "<li class='" . ($mitgliedschaft->mitgliedschaftAktiv() ? 'aktiv' : 'inaktiv') . "'>";
                                    echo CHtml::encode($gremium->getName(true));
                                    if ($gremium->ba_nr > 0) {
                                        echo " (Bezirksausschuss " . CHtml::link($gremium->ba->name, $gremium->ba->getLink()) . ")";
                                    }
                                    if ($mitgliedschaft->datum_bis != "") {
                                        echo '<br><small>(' . RISTools::datumstring($mitgliedschaft->datum_von);
                                        echo ' bis ' . RISTools::datumstring($mitgliedschaft->datum_bis) . ')</small>';
                                    }
                                    echo "</li>\n";
                                }
                                ?>
                            </ul>
                        </td>
                    </tr>
                <?php
                }
                if (count($person->antraege) > 0) {
                    ?>
                    <tr>
                        <th>Anträge:</th>
                        <td id="list-js-container">
                            <input class="search" placeholder="Filtern" style="margin-left: 40px; width: calc(100% - 40px);" /> <?php /* Nicht ganz sauber, aber besser als nichts */ ?>
                            <ul class="list">
                                <?php
                                foreach ($person->antraege as $antrag) {
                                    echo "<li>";
                                    echo "<a class='antrag-name' href='" . CHtml::encode($antrag->getLink()) . "'>" . $antrag->getName(true) . "</a>";
                                    echo "<span class='list-js-datum'> (" . RISTools::datumstring($antrag->gestellt_am) . ")</span>";
                                    echo "</li>\n";
                                }
                                ?>
                            </ul>
                        </td>
                    </tr>
                <?php
                } else {
                    $suche = new RISSucheKrits();
                    $suche->addKrit('volltext', "\"" . $person->getName() . "\"");
                    $solr   = RISSolrHelper::getSolrClient();
                    $select = $solr->createSelect();
                    $suche->addKritsToSolr($select);
                    $select->setRows(50);
                    $select->addSort('sort_datum', $select::SORT_DESC);

                    /** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
                    $hl = $select->getHighlighting();
                    $hl->setFields('text, text_ocr, antrag_betreff');
                    $hl->setSimplePrefix('<b>');
                    $hl->setSimplePostfix('</b>');

                    /** @var \Solarium\QueryType\Select\Result\Result $ergebnisse */
                    $ergebnisse     = $solr->select($select);
                    $solr_dokumente = $ergebnisse->getDocuments();

                    if (count($solr_dokumente) > 0) {
                        echo '<tr><th>Namentlich erwähnt in:</th><td><ul>';
                        $highlighting = $ergebnisse->getHighlighting();
                        foreach ($solr_dokumente as $solr_dokument) {
                            $dokument = Dokument::getDocumentBySolrId($solr_dokument->id, true);

                            // Fehlerfälle abfangen
                            if (!$dokument) {
                                if ($this->binContentAdmin())
                                    echo "<li>Dokument nicht gefunden: " . $solr_dokument->id . "</li>";
                                continue;
                            } elseif (!$dokument->getRISItem()) {
                                if ($this->binContentAdmin())
                                    echo "<li>Dokument-Zuordnung nicht gefunden: " . $solr_dokument->typ . " / " . $solr_dokument->id . "</li>";
                                continue;
                            }

                            $risitem = $dokument->getRISItem();
                            if (!$risitem) continue;

                            /**
                             * Design eines Eintrags:
                             *  Oben: Der Titel des Antrags/Termins/etc.
                             *  Unten: [Download-Button] Name des Dokuments
                             */
                            ?>
                            <li style="margin-bottom: 10px;">
                                <?= CHtml::link($risitem->getName(true), $risitem->getLink()) ?> <br>
                                <a class="fontello-download antrag-herunterladen"
                                   href="<?= CHtml::encode($dokument->getLinkZumDownload()) ?>"
                                   download="<?= $dokument->antrag_id ?> - <?= CHtml::encode($dokument->getName(true)) ?>.pdf"
                                   title="Herunterladen: <?= CHtml::encode($dokument->getName(true)) ?>">
                                </a>
                                <?= CHtml::link($dokument->getName(false), $dokument->getLink()) ?>
                            </li> <?php
                        }
                        echo '</ul></td></tr>';
                    }
                } ?>
                </tbody>
            </table>
        </section>
    </div>
    <section class="col-md-4">
        <div class="well personendaten_sidebar" itemprop="description">
            <h2>Weitere Infos</h2>
            <dl>
                <?php
                if ($person->web != "") {
                    echo '<dt>Homepage:</dt>';
                    echo '<bb>' . HTMLTools::textToHtmlWithLink($person->web) . '</dd>' . "\n";
                }
                if ($person->twitter != "") {
                    echo '<dt>Twitter:</dt>';
                    if (preg_match("/twitter\.com\/(.*)/siu", $person->twitter, $matches)) {
                        $twitter = $matches[1];
                    } else {
                        $twitter = $person->twitter;
                    }
                    echo '<dd><a href="https://twitter.com/' . CHtml::encode($twitter) . '">@' . CHtml::encode($twitter) . '</a></dd>' . "\n";
                }
                if ($person->facebook != "") {
                    echo '<dt>Facebook:</dt>';
                    echo '<dd><a href="https://www.facebook.com/' . CHtml::encode($person->facebook) . '">Facebook-Profil</a></dd>' . "\n";
                }
                if ($person->abgeordnetenwatch != "") {
                    echo '<dt>Abgeordnetenwatch:</dt>';
                    echo '<dd><a href="' . CHtml::encode($person->abgeordnetenwatch) . '">Abgeordnetenwatch-Profil</a></dd>' . "\n";
                }
                if ($person->geburtstag != "") {
                    $datum = explode("-", $person->geburtstag);
                    if ($datum[1] > 0) {
                        echo '<dt>Geburtstag:</dt>';
                        echo '<dd>' . RISTools::datumstring($person->geburtstag) . '</dd>' . "\n";
                    } else {
                        echo '<dt>Geburtsjahr:</dt>';
                        echo '<dd>' . CHtml::encode($datum[0]) . '</dd>' . "\n";
                    }
                }
                if ($person->beschreibung != "") {
                    echo '<dt>Beschreibung</dt>';
                    echo '<dd>' . nl2br(CHtml::encode($person->beschreibung));
                    if ($person->quellen != "") echo '<div class="quelle">Quelle: ' . CHtml::encode($person->quellen) . '</div>';
                    echo '</dd>' . "\n";
                }
                ?>
            </dl>
            <?php
            $ich = $this->aktuelleBenutzerIn();
            if ($ich && $ich->id == $person->benutzerIn_id) {
                $editlink = Yii::app()->createUrl("personen/personBearbeiten", array("id" => $person->id));
                echo '<a href="' . CHtml::encode($editlink) . '"><span class="mdi-content-create"></span> Eintrag bearbeiten</a>';
            } else {
                $binichlink = Yii::app()->createUrl("personen/binIch", array("id" => $person->id));
                $login_add = ($ich ? '' : ' <small>(Login)</small>');
                echo '<a href="' . CHtml::encode($binichlink) . '" style="font-size: 0.9em; color: gray; font-style: italic;">Sie Sind ' . CHtml::encode($person->getName()) . '?' . $login_add . '</a>';
            }
            ?>
        </div>
    </section>
</div>

<?php $this->load_list_js = true; ?>

<script>
var userList = new List("list-js-container", { valueNames: [ 'antrag-name', 'list-js-datum' ] });
</script>
