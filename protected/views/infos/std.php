<?
/**
 * @var string $my_url
 * @var Text $text
 * @var bool $show_title
 */

$this->pageTitle = $text->titel;

$html_text = preg_replace_callback("/CREATE_URL\((?<url>[^\)]+)\)/siu", function($matches) {
    return CHtml::encode(Yii::app()->createUrl($matches["url"]));
}, $text->text);
?>

<section class="well std_fliesstext">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li class="active"><?=CHtml::encode($text->titel)?></li>
    </ul>

    <?
    $this->renderPartial("/index/ckeditable_text", array(
        "text"            => $text,
        "my_url"          => $my_url,
        "show_title"      => $show_title,
        "insert_tooltips" => false,
    ))
    ?>

</section>
