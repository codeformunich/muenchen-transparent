<?php
/**
 * @var int ortsbezugszahlgrenze
 * @var array $geodata_overflow
 */
?>
<div class="map-container">
    <div id="mapholder">
        <div id="map"></div>
    </div>

    <div id="overflow_hinweis" <?php if (count($geodata_overflow) == 0) echo "style='display: none;'"; ?>>
        <label><input type="checkbox" name="zeige_overflow">
            Zeige <span class="anzahl"><?= (count($geodata_overflow) == 1 ? "1 Dokument" : count($geodata_overflow) . " Dokumente") ?></span> mit über <?= $ortsbezugszahlgrenze ?> Ortsbezügen
        </label>
    </div>

    <div id="benachrichtigung_hinweis">
        <div id="benachrichtigung_hinweis_text">
            <div class="nichts">
                Du kannst dich bei neuen Dokumenten mit Bezug zu einem bestimmten Ort per E-Mail benachrichtigen lassen.<br>
                Klicke dazu auf einen Ort und stelle dann den relevanten Radius ein.<br>
            </div>
            <div class="infos" style="display: none;">
                <strong>Ausgew&auml;hlt:</strong> <span class="radius_m"></span> Meter um "<span class="zentrum_ort"></span>" (ungefähr)<br>
                <br>Willst du per E-Mail benachrichtigt werden, wenn neue Dokumente mit diesem Ortsbezug erscheinen?
            </div>
            <form method="POST" action="<?= CHtml::encode($this->createUrl("benachrichtigungen/index")) ?>">
                <input type="hidden" name="geo_lng" value="">
                <input type="hidden" name="geo_lat" value="">
                <input type="hidden" name="geo_radius" id="geo_radius" value="">
                <input type="hidden" name="krit_str" value="">

                <div>
                    <button class="btn btn-primary benachrichtigung_add_geo" disabled name="<?= AntiXSS::createToken("benachrichtigung_add_geo") ?>" type="submit">Benachrichtigen!</button>
                </div>
            </form>
        </div>
    </div>
</div>
