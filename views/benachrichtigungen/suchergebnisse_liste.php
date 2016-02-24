<?php
/**
 * @var RISBaseController $this
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 */

$dokumente = $ergebnisse->getDocuments();

if (count($dokumente) == 0) {
    ?>
    <div class="alert alert-error">
        Keine Dokumente gefunden.
    </div>
<?
} else {
    ?>
    <ul class="list-group">
        <?
        //$mlt = $ergebnisse->getMoreLikeThis();
        //$ergebnisse->getMoreLikeThis();
        $highlighting = $ergebnisse->getHighlighting();
        foreach ($dokumente as $dokument) {
            $dok = Dokument::getDocumentBySolrId($dokument->id, true);
            if (!$dok) {
                if ($this->binContentAdmin()) {
                    echo "<li class='list-group-item'>Dokument nicht gefunden: " . $dokument->id . "</li>";
                }
            } elseif (!$dok->getRISItem()) {
                if ($this->binContentAdmin()) {
                    echo "<li class='list-group-item'>Dokument-Zuordnung nicht gefunden: " . $dokument->typ . " / " . $dokument->id . "</li>";
                }
            } else {
                $risitem = $dok->getRISItem();
                if (!$risitem) continue;

                $dokurl = $dok->getLinkZumDokument();
                ?>
                <li class='list-group-item'>
                    <div class="row-action-primary">
                        <i class="glyphicon glyphicon-file"></i>
                    </div>
                    <div class="row-content">
                        <h4 class="list-group-item-heading">
                            <a href="<?=CHtml::encode($risitem->getLink())?>" title="<?=CHtml::encode($risitem->getName()) ?>" class="overflow-fadeout-white"><span>
                                <span class="least-content"><?= CHtml::encode($dok->getDisplayDate()) ?></span>
                                <?=CHtml::encode($risitem->getName(true))?>
                            </span></a>
                        </h4>

                        <p class="list-group-item-text">
                            <?
                            echo '<a href="' . CHtml::encode($dokurl) . '" class="dokument"><span class="fontello-download"></span> ' . CHtml::encode($dok->getName(false)) . '</a><br>';
                            $highlightedDoc = $highlighting->getResult($dokument->id);
                            if ($highlightedDoc && count($highlightedDoc) > 0) {
                                foreach ($highlightedDoc as $field => $highlight) {
                                    echo implode(' (...) ', $highlight) . '<br/>';
                                }
                            }
                            ?>
                            <span class="border">&nbsp;</span>
                        </p>
                    </div>
                </li>
            <?
            }
        }?>
    </ul>
<? }
