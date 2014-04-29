<?php
/**
 * @var Termin $termin
 * @var TermineController $this
 */

$this->pageTitle = $termin->getName();

$assets_base = $this->getAssetsBase();


?>
<h1><?= CHtml::encode($termin->getName()) ?></h1>

<table class="table table-bordered">
	<tbody>
	<tr>
		<th>Originallink:</th>
		<td><?= CHtml::link($termin->getSourceLink(), $termin->getSourceLink()) ?></td>
	</tr>
	<tr>
		<th>Dokumente:</th>
		<td>
			<ul>
				<? foreach ($termin->antraegeDokumente as $dok) {
					echo "<li>" . CHtml::link($dok->name, $this->createUrl("index/dokument", array("id" => $dok->id))) . "</li>";
				} ?>
			</ul>
		</td>
	</tr>
	<tr>
		<th>Ergebnisse:</th>
		<td>
			<ul>
				<? foreach ($termin->antraegeErgebnisse as $dok) {
					if (!is_object($dok->antrag)) echo "<li>" . CHtml::encode($dok->top_betreff) . ": " . CHtml::encode($dok->beschluss_text) . "</li>";
					else echo "<li>" . CHtml::link($dok->antrag->getName(), $dok->antrag->getLink()) . ": " . CHtml::encode($dok->beschluss_text) . "</li>";
				} ?>
			</ul>
		</td>
	</tr>
	</tbody>
</table>
