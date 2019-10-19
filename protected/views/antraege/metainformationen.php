<?php
/**
 * @var Antrag $antrag
 * @var bool $zeige_ba_orte
 */
?>
<div class='metainformationen_antraege'>

<?php
$parteinliste = array();
$parteien = $antrag->findeFraktionen();
foreach ($parteien as $partei) {
    $parteinliste[] = "<span class='partei' title='" . CHtml::encode(implode(", ", $partei["mitglieder"])) . "'>" . CHtml::encode($partei["name"]) . "</span>";
}
if (count($parteinliste) > 0) echo implode(", ", $parteinliste) . ", ";

if ($antrag->ba_nr > 0) echo "<span title='" . CHtml::encode("Bezirksausschuss " . $antrag->ba_nr . " (" . $antrag->ba->name . ")") . "' class='ba'>BA " . $antrag->ba_nr . "</span>, ";

$ts = $antrag->getDokumentenMaxTS();
if ((isset($zeige_jahr) && $zeige_jahr) || (date("Y") != date("Y", $ts))) {
    echo date("d.m.Y", $ts);
} else {
    echo date("d.m.", $ts);
}

if ($zeige_ba_orte > 0 && $antrag->ba_nr != $zeige_ba_orte) {
    /** @var string[] $orte */
    $orte = array();
    foreach ($antrag->orte as $ort) if ($ort->ort->ba_nr == $zeige_ba_orte) $orte[] = $ort->ort->ort;
    if (count($orte) > 0) echo '<span class="glyphicon glyphicon-map-marker" title="' . implode(", ", $orte) . '"></span>';
}
?>

</div>
