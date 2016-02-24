<?php


/**
 * @var TermineController $this
 * @var array $termine
 */

?>
<section class="well">
    <table style="width: 100%;">
        <thead>
        <tr>
            <th>BA</th>
            <th>Termin</th>
            <th>Name</th>
            <th>Ort</th>
        </tr>
        </thead>
        <tbody>
        <?
        foreach ($termine as $termin) {
            echo "<tr>";
            echo "<td><strong>" . $termin["ba_nr"] . "</strong></td>";
            echo "<td>" . $termin["termin"] . "</td>";
            echo "<td>" . $termin["name"] . "</td>";
            echo "<td>" . $termin["sitzungsort"] . "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</section>