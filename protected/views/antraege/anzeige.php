<?php
/**
 * @var Antrag $antrag
 * @var AntraegeController $this
 */

$this->pageTitle = $antrag->getName();

$assets_base = $this->getAssetsBase();


$personen = array(
	AntragPerson::$TYP_GESTELLT_VON => array(),
	AntragPerson::$TYP_INITIATORIN  => array(),
);
foreach ($antrag->antraegePersonen as $ap) $personen[$ap->typ][] = $ap->person;

?>
<h1><?= $antrag->getName() ?></h1>

<table class="table table-bordered">
	<tbody>
	<tr>
		<th>Originallink:</th>
		<td><?= CHtml::link($antrag->getSourceLink(), $antrag->getSourceLink()) ?></td>
	</tr>
	<? if (count($personen[AntragPerson::$TYP_INITIATORIN])) { ?>
		<tr>
			<th>Initiiert von:</th>
			<td>
				<ul>
					<?
					foreach ($personen[AntragPerson::$TYP_INITIATORIN] as $person) {
						echo "<li>";
						/** @var Person $person */
						if ($person->stadtraetIn) {
							echo CHtml::link($person->stadtraetIn->name, "#");
							echo " (" . CHtml::encode($person->ratePartei($antrag->gestellt_am)) . ")";
						} else {
							echo CHtml::encode($person->name);
						}
						echo "</li>\n";
					}
					?>
				</ul>
			</td>
		</tr>
	<? }
	if (count($personen[AntragPerson::$TYP_GESTELLT_VON])) { ?>
		<tr>
			<th>Gestellt von:</th>
			<td>
				<ul>
					<?
					foreach ($personen[AntragPerson::$TYP_GESTELLT_VON] as $person) {
						echo "<li>";
						/** @var Person $person */
						if ($person->stadtraetIn) {
							echo CHtml::link($person->stadtraetIn->name, "#");
							echo " (" . CHtml::encode($person->ratePartei($antrag->gestellt_am)) . ")";
						} else {
							echo CHtml::encode($person->name);
						}
						echo "</li>\n";
					}
					?>
				</ul>
			</td>
		</tr>
	<? }
	?>
	<tr>
		<th>Dokumente:</th>
		<td><ul>
		<? foreach ($antrag->dokumente as $dok) {
			echo "<li>" . date("d.m.Y", RISTools::date_iso2timestamp($dok->datum)) . ": " . CHtml::link($dok->name, $this->createUrl("index/dokument", array("id" => $dok->id))) . "</li>";
		} ?>
		</ul></td>
	</tr>
	<? if (count($antrag->antrag2vorlagen) > 0) { ?>
		<tr>
			<th>Verbundene Stadtratsvorlagen:</th>
			<td><ul>
					<? foreach ($antrag->antrag2vorlagen as $vorlage) {
						echo "<li>" . CHtml::link($vorlage->getName(), $this->createUrl("antraege/anzeigen", array("id" => $vorlage->id))) . "</li>";
					} ?>
				</ul></td>
		</tr>
	<? }
	if (count($antrag->vorlage2antraege) > 0) { ?>
		<tr>
			<th>Verbundene Stadtratsanträge:</th>
			<td><ul>
					<? foreach ($antrag->vorlage2antraege as $antrag2) {
						echo "<li>" . CHtml::link($antrag2->getName(), $this->createUrl("antraege/anzeigen", array("id" => $antrag2->id))) . "</li>";
					} ?>
				</ul></td>
		</tr>
	<? } ?>
	</tbody>
</table>
