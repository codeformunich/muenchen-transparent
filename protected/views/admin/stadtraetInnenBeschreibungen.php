<?php
/**
 * @var AntraegeController $this
 * @var array[] $fraktionen
 */


?>

<section class="well">
    <h1>Stadtratsmitglieder: Beschreibungen</h1>

    <form method="POST" style="overflow: auto; font-size: 12px;">
        <table style="width: 100%;">
            <thead>
            <tr>
                <th>Name</th>
                <th>Geschlecht</th>
                <th>Geburtstag</th>
                <th>Beschreibung</th>
                <th>Quellen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fraktionen as $fraktion) foreach ($fraktion as $str) {
                /** @var StadtraetIn $str */
                ?>
                <tr>
                    <td style="font-size: 14px; padding-top: 20px;"><?= CHtml::encode($str->getName()) ?></td>
                    <td>
                        <?php foreach (StadtraetIn::GESCHLECHTER as $sex_key => $sex_name) {
                            echo '<label style="font-weight: normal;"><input type="radio" name="geschlecht[' . $str->id . ']" value="' . $sex_key . '" ';
                            if ($str->geschlecht == $sex_key) echo 'checked';
                            echo '> ' . CHtml::encode($sex_name) . '</label><br>';
                        } ?>
                    </td>
                    <td style="padding-top: 20px;"><input type="text" name="geburtstag[<?= $str->id ?>]" placeholder="YYYY-MM-DD" title="Geburtstag" value="<?= CHtml::encode($str->geburtstag) ?>" maxlength="15"></td>
                    <td style="padding-top: 20px;"><textarea name="beschreibung[<?=$str->id?>]" title="Beschreibung" rows="3" style="width: 230px;"><?=CHtml::encode($str->beschreibung)?></textarea></td>
                    <td style="padding-top: 20px;"><textarea name="quellen[<?=$str->id?>]" title="Quellen" rows="3"><?=CHtml::encode($str->quellen)?></textarea></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <div style="position: fixed; bottom: 0; left: 45%;">
            <button type="submit" class="btn btn-primary" name="<?= AntiXSS::createToken("save") ?>">Speichern</button>
        </div>
    </form>
</section>
