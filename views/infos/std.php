<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var string $my_url
 * @var Text $text
 * @var bool $show_title
 */

$this->title = $text->titel;

$html_text = preg_replace_callback("/CREATE_URL\((?<url>[^\)]+)\)/siu", function($matches) {
    return Html::encode(Url::to($matches["url"]));
}, $text->text);
?>

<section class="well std_fliesstext">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= Html::encode(Url::to("index/startseite")) ?>">Startseite</a><br></li>
        <li class="active"><?=Html::encode($text->titel)?></li>
    </ul>

    <?
    echo $this->render("/index/ckeditable_text", array(
        "text"            => $text,
        "my_url"          => $my_url,
        "show_title"      => $show_title,
        "insert_tooltips" => false,
    ))
    ?>

</section>
