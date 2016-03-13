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

                <div class="col col-md-2"><button type="submit" class="btn btn-primary" name="<?= AntiXSS::createToken("tag_umbennen") ?>" style="postion: absolute; top: -10px;">Umbennen</button></div>
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

                <div class="col col-md-2"><button type="submit" class="btn btn-danger" name="<?= AntiXSS::createToken("tag_loeschen") ?>" style="postion: absolute; top: -10px; left: 10px">Löschen</button></div>
            </div>
        </fieldset>
    </form>

    <h3>Alle Tags</h3>
    <div id="tag-liste" class="table_sortiert">
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
                        <td class="tag-name" ><?= $tag->name ?></td>
                        <td class="antrag-id"><?= CHtml::link($antrag->id, $antrag->getLink()) ?></td>
                        <td class="email"    ><?= $tag->angelegt_benutzerIn->email ?></td>
                        <td class="fontello-cancel tag-delete"></td>
                    </tr>
                <? } ?>
            <? } ?>
            <tbody>
        </table>
    </div>

</section>

<style>
input.search {
    margin-bottom: 10px;
}

td.fontello-cancel {
    font-size: 18px;
    text-align: center;
}

td.fontello-cancel:hover {
    cursor: pointer;
    color: red;
}
</style>

<script src="/bower/list.js/dist/list.min.js"></script>

<script>
var userList = new List("tag-liste", {valueNames: ["tag-name", "antrag-id", "email"]});
</script>

<script>
$(".tag-delete").click(function() {
    form = $("<form>", {
        "action": "<?= CHtml::encode($this->createUrl('admin/tags')) ?>",
        "method": "POST"
    }).append($("<input>", {
        "name": "tag_name",
        "value": $(this).prevAll(".tag-name").text(),
    })).append($("<input>", {
        "name": "antrag_id",
        "value": $(this).prevAll(".antrag-id").text(),
    })).append($("<input>", {
        "name": "<?= AntiXSS::createToken("einzelnen_tag_loeschen") ?>",
        "value": "",
    }));
    $("body").append(form);
    form.submit();
    
});
</script>
