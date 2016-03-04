<?php

use yii\helpers\Html;

/**
 * @var Dokument[] $dokumente
 */

echo '<ul>';
foreach ($dokumente as $dok) {
	echo '<li>';
	echo Html::a($dok->name, $dok->getLinkZumDokument()) . " (" . $dok->seiten_anzahl . " Seiten): ";
	if ($dok->antrag_id > 0) echo Html::a($dok->antrag->getName(true), $dok->antrag->getLink());
	echo '</li>';
}
echo '</ul>';
