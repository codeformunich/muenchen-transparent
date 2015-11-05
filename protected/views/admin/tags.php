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

    <h3>Alle Tags</h3>
    <div id="tag-liste" class="sortiert">
        <input class="search" placeholder="Filtern" style="margin-bottom: 10px;"/>
        <table style="width: 100%">
            <tr>
                <th><button class="sort" data-sort="tag-name" >Tag    </button></th>
                <th><button class="sort" data-sort="antrag-id">Antrag </button></th>
                <th><button class="sort" data-sort="email"    >E-Mail </button></th>
                <th><button class="sort"                      >Löschen</button></th>
            </tr>
            <tbody class="list">
            <? foreach($tags as $tag) { ?>
                <? foreach($tag->antraege as $antrag) { ?>
                    <tr>
                        <td class="tag-name"       ><?= $tag->name ?></td>
                        <td class="antrag-id"      ><?= CHtml::link($antrag->id, $antrag->getLink()) ?></td>
                        <td class="email"          ><?= $tag->angelegt_benutzerIn->email ?></td>
                        <td class="fontello-cancel"></td>
                    </tr>
                <? } ?>
            <? } ?>
            <tbody>
        </table>
    </div>

</section>

<script src="/js/list.js/dist/list.min.js"></script>
<script>
var options = {
    valueNames: ["tag-name", "antrag-id", "email"]
};

var userList = new List('tag-liste', options);
</script>
