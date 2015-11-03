<?php
/**
 * @var AdminController $this
 */

$user = $this->aktuelleBenutzerIn();

?>

<section class="well">

    <form method="POST" style="overflow: auto;" action="<?= CHtml::encode($this->createUrl("admin/tags")) ?>" >
        <fieldset>
            <legend>Tag Umbennen</legend>
                <div class="input-group col">
                <div class="col col-md-6"><select name="tag_id">
                <?
                foreach($tags as $tag)
                    echo "<option value=" . $tag->id . ">" . $tag->name . "</option>";
                ?>
                </select></div>

                <div class="col col-md-4"><input type="text" placeholder="Neuer Name des Tags" name="neuer_name" class="form-control"></div>

                <div class="col col-md-2"><button type="submit" class="btn btn-primary" name="<?=AntiXSS::createToken("tag_umbennen")?>" style="postion: absolute; top: -10px;">Umbennen</button></div>
            </div>
        </fieldset>
    </form>

    <form method="POST" style="overflow: auto;" action="<?= CHtml::encode($this->createUrl("admin/tags")) ?>" >
        <fieldset>
            <legend>Tag Löschen</legend>
                <div class="input-group col">
                <div class="col col-md-6"><select name="tag_id">
                <?
                foreach($tags as $tag)
                    echo "<option value=" . $tag->id . ">" . $tag->name . "</option>";
                ?>
                </select></div>

                <div class="col col-md-2"><button type="submit" class="btn btn-danger" name="<?=AntiXSS::createToken("tag_loeschen")?>" style="postion: absolute; top: -10px; left: 10px">Löschen</button></div>
            </div>
        </fieldset>
    </form>


    <table style="width: 100%">
    <tr><th>Tag</th><th>Antrag</th><th>E-Mail</th><th>Löschen</th></tr>
    <? foreach($tags as $tag) { ?>
        <? foreach($tag->antraege as $antrag) { ?>
            <tr>
                <td><?= $tag->name ?></td>
                <td><?= CHtml::link($antrag->id, $antrag->getLink()) ?></td>
                <td><?= $tag->angelegt_benutzerIn->email ?></td>
                <td class="fontello-cancel" style="font-size: 18px"></td>
            </tr>
        <? } ?>
    <? } ?>
    </table>

</section>
