<?php
/**
 * @var string $my_url
 * @var Text $text
 * @var bool $show_title
 * @var bool $insert_tooltips
 */

$html_text = preg_replace_callback("/CREATE_URL\((?<url>[^\)]+)\)/siu", function($matches) {
    return CHtml::encode(Yii::app()->createUrl($matches["url"]));
}, $text->text);
if ($insert_tooltips) $html_text = RISTools::insertTooltips($html_text);
?>

<? if ($this->binContentAdmin()) { ?>
    <a href="#" style="display: inline; float: right;" id="text_edit_caller">
        <span class="mdi-content-create"></span> Bearbeiten
    </a>
    <a href="#" style="display: none; float: right;" id="text_edit_aborter">
        <span class="mdi-content-clear"></span> Abbrechen
    </a>
<? }

if ($show_title) echo '<h1>' . CHtml::encode($text->titel) . '</h1>';

if ($this->binContentAdmin()) { ?>
    <script src="/js/ckeditor/ckeditor.js"></script>
    <div id="text_content_holder" style="border: dotted 1px transparent;">
        <?=$html_text?>
    </div>
    <form method="POST" action="<?= CHtml::encode($my_url) ?>" id="text_edit_form" style="display: none; border: dotted 1px gray;">
        <div id="text_orig_holder"><?= $text->text ?></div>
        <input type="hidden" name="text" value="<?= CHtml::encode($text->text) ?>">
        <div style="text-align: center;">
            <button type="submit" name="<?= CHtml::encode(AntiXSS::createToken("save")) ?>" class="btn btn-primary">Speichern</button>
        </div>
    </form>

    <script>
    $("#text_edit_caller").click(function(ev) {
        ev.preventDefault();
        $("#text_edit_caller").hide();
        $("#text_edit_aborter").show();
        $("#text_content_holder").hide();
        $("#text_edit_form").show();
        ckeditor_init($("#text_orig_holder"), "inline");
    });
    $("#text_edit_aborter").click(function(ev) {
        ev.preventDefault();
        $("#text_edit_caller").show();
        $("#text_edit_aborter").hide();
        $("#text_content_holder").show();
        $("#text_edit_form").hide();
    });
    $("#text_edit_form").submit(function() {
        $(this).find("input[name=text]").val(CKEDITOR.instances["text_orig_holder"].getData());
    });
    </script>

<? } else {
    echo $html_text;
} ?>
