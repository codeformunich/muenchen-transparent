<?php
/**
 * @var Antrag $antrag
 * @var AntraegeController $this
 */

$this->pageTitle = $antrag->getName();

$assets_base = $this->getAssetsBase();




?>
<h1><?=$antrag->getName()?></h1>

<table class="table table-bordered">
	<tbody>
	<tr>
		<th>Originallink:</th>
		<td><?=CHtml::link($antrag->getSourceLink(), $antrag->getSourceLink())?></td>
	</tr>
	</tbody>
</table>
