<?php
/**
 * @var RISBaseController $this
 * @var \Solarium\QueryType\Select\Result\Grouping\FieldGroup $fieldGroup
 * @var Solarium\QueryType\Select\Result\Highlighting\Result $highlighting
 */

?>

<? if ($fieldGroup->getMatches() == 0) { ?>
    <p class="text-danger" style="text-align: center; font-size: xx-large">Nichts gefunden.</p>
<? } ?>

<ul class="list-group">
    <?

    foreach ($fieldGroup as $valueGroup) {
        echo '<h2>----' . (int)$valueGroup->getValue() . '</h2>';

        ?>

        <li class='list-group-item'>
            <div class="row-action-primary">
                <i class="glyphicon glyphicon-file"></i>
            </div>

            <div class="row-content">

                <?
                foreach ($valueGroup as $dokument) {
                    $dok = Dokument::getDocumentBySolrId($dokument->id, true);
                    if (!$dok) {
                        if ($this->binContentAdmin()) {
                            echo "<li class='list-group-item'>Dokument nicht gefunden: " . $dokument->id . "</li>";
                        }
                        continue;
                    }
                    $risitem = $dok->getRISItem();
                    if (!$risitem) {
                        if ($this->binContentAdmin()) {
                            echo "<li class='list-group-item'>Dokument-Zuordnung nicht gefunden: " . $dokument->typ . " / " . $dokument->id . "</li>";
                        }
                        continue;
                    }
                    ?>


                    <h4 class="list-group-item-heading">
                        <a href="<?= CHtml::encode($risitem->getLink()) ?>"
                           title="<?= CHtml::encode($risitem->getName()) ?>" class="overflow-fadeout-white"><span>
                        <span class="least-content"><?= CHtml::encode($dok->getDisplayDate()) ?></span>
                                <?= CHtml::encode($risitem->getName(true)) ?>
                    </span></a>
                    </h4>

                    <p class="list-group-item-text">
                        <?
                        echo '<a href="' . CHtml::encode($dok->getLink()) . '" class="dokument"><span class="fontello-download"></span> ' . CHtml::encode($dok->getName(false)) . '</a><br>';
                        $highlightedDoc = $highlighting->getResult($dokument->id);
                        if ($highlightedDoc && count($highlightedDoc) > 0) {
                            foreach ($highlightedDoc as $field => $highlight) {
                                echo implode(' (...) ', $highlight) . '<br/>';
                            }
                        }
                        ?>
                        <span class="border">&nbsp;</span>
                    </p>
                <? } ?>
            </div>
        </li>
    <? } ?>
</ul>

