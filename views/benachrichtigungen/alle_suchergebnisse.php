<?php


/**
 * @var BenachrichtigungenController $this
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 */



?>
<h1>Alle Suchergebnisse</h1>

<?

echo $this->render("suchergebnisse_liste", array(
	"ergebnisse"  => $ergebnisse,
));