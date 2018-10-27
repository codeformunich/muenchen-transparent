<?php

/**
 * @var BenachrichtigungenController $this
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 */


?>
<section class="well suchergebnisse">
    <h1>Alle Suchergebnisse</h1>
    <?

    $this->renderPartial("suchergebnisse_liste", array(
        "ergebnisse" => $ergebnisse,
    ));

    ?>
</section>
