<?php

/**
 * @var BenachrichtigungenController
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 */
?>
<h1>Alle Suchergebnisse</h1>

<?

$this->renderPartial("suchergebnisse_liste", array(
	"ergebnisse"  => $ergebnisse,
));