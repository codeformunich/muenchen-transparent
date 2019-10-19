<?php
/**
 * @var AdminController $this
 * @var Termin[] $termine
 */

?>
<section class="well">
    <h1>BürgerInnenversammlungen</h1>

    <form method="POST" action="<?=CHtml::encode(Yii::app()->createUrl("admin/buergerInnenversammlungen"))?>">
        <table>
            <thead>
            <tr>
                <th>Bezirksausschuss</th>
                <th>Termin</th>
                <th>Ort</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($termine as $termin) {
                $id      = $termin->id;
                $del_url = Yii::app()->createUrl("admin/buergerInnenversammlungen", array(AntiXSS::createToken("delete") => $id));
                ?>
                <tr>
                    <td>
                        <?= CHtml::encode("BA " . $termin->ba->ba_nr . ": " . $termin->ba->name) ?>
                    </td>
                    <td>
                        <input type="text" name="termin[<?= $id ?>][datum]" placeholder="YYYY-MM-DD HH:II:SS" size="20" value="<?= CHtml::encode($termin->termin) ?>">
                    </td>
                    <td>
                        <textarea cols="50" rows="3" name="termin[<?= $id ?>][ort]"><?= CHtml::encode($termin->sitzungsort) ?></textarea>
                    </td>
                    <td>
                        <a href="<?= CHtml::encode($del_url) ?>" onclick="return confirm('Wirklich löschen?');" style="color: red;">löschen</a>
                    </td>
                </tr>
            <?php
            } ?>

            <tr>
                <th colspan="3" style="font-size: 16px; padding-top: 50px;">Neuen Eintrag anlegen</th>
            </tr>
            <tr>
                <td style="padding: 5px;"><select name="neu[ba_nr]" size="1" style="max-width: 200px;">
                        <option value=""></option>
                        <?php
                        /** @var Bezirksausschuss[] $bas */
                        $bas = Bezirksausschuss::model()->findAll(array("order" => "ba_nr"));
                        foreach ($bas as $ba) {
                            echo '<option value="' . $ba->ba_nr . '">BA ' . $ba->ba_nr . ': ' . CHtml::encode($ba->name) . '</option>' . "\n";
                        }
                        ?>
                    </select></td>
                <td style="padding: 5px;">
                    <input type="text" name="neu[datum]" placeholder="YYYY-MM-DD HH:II:SS" size="20">
                </td>
                <td style="padding: 5px;">
                    <textarea cols="50" rows="3" name="neu[ort]"></textarea>
                </td>
                <td style="padding: 5px;"></td>
            </tr>
            </tbody>
        </table>

        <div style="text-align: center;">
            <button type="submit" class="btn btn-primary" name="<?= AntiXSS::createToken("save") ?>">Speichern</button>
        </div>
    </form>
</section>
