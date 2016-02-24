<?php

use yii\helpers\Html;
use app\components\AntiXSS;

/**
 * @var AntraegeController $this
 * @var array[] $fraktionen
 */


?>

<section class="well">
    <h1>StadträtInnen: Social Media Links</h1>

    <form method="POST" style="overflow: auto;">
        <table style="width: 100%;">
            <thead>
            <tr>
                <th>Name</th>
                <th>Fraktion</th>
                <th>Homepage</th>
                <th>Twitter</th>
                <th>Facebook-URL</th>
                <th>Abgeordnetenwatch-URL</th>
            </tr>
            </thead>
            <tbody>
            <? foreach ($fraktionen as $fraktion) foreach ($fraktion as $str) {
                /** @var StadtraetIn $str */
                ?>
                <tr>
                    <td><?= Html::encode($str->name) ?></td>
                    <td><?= Html::encode($str->stadtraetInnenFraktionen[0]->fraktion->name) ?></td>
                    <td><input name="web[<?= $str->id ?>]" title="Homepage" value="<?= Html::encode($str->web) ?>" width="60" maxlength="200"></td>
                    <td style="white-space: nowrap;">@<input name="twitter[<?= $str->id ?>]" title="Twitter" value="<?= Html::encode($str->twitter) ?>" maxlength="45"></td>
                    <td><input name="facebook[<?= $str->id ?>]" title="Facebook" value="<?= Html::encode($str->facebook) ?>" width="60" maxlength="200"></td>
                    <td><input name="abgeordnetenwatch[<?= $str->id ?>]" title="Twitter" value="<?= Html::encode($str->abgeordnetenwatch) ?>" width="60" maxlength="200"></td>
                </tr>
            <? } ?>
            </tbody>
        </table>

        <div style="position: fixed; bottom: 0; left: 45%;">
            <button type="submit" class="btn btn-primary" name="<?= AntiXSS::createToken("save") ?>">Speichern</button>
        </div>
    </form>
</section>
