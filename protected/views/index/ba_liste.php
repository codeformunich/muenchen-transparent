<?php
/** @var Bezirksausschuss[] $bas */
/** Liste zum Auswählen eines Ba's auf Geräten mit kleinem Bildschirm */
?>
<section class="well">
    <h2>Die Bezirkausschüsse</h2>
    <ul class="baliste">
        <?php foreach ($bas as $ba) echo "<li>" . CHtml::link($ba->ba_nr . ": " . $ba->name, $ba->getLink()) . "</li>\n"; ?>
    </ul>
</section>
