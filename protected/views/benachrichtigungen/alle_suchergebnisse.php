<?php

/**
 * @var BenachrichtigungenController $this
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 */



?>
<h1>Alle Suchergebnisse</h1>

<?

$this->renderPartial("suchergebnisse_liste_benachrichtigungen", array(
	"ergebnisse"  => $ergebnisse,
));
