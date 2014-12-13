<?
/**
 * @var Rechtsdokument $dokument
 * @var InfosController $this
 */



$this->pageTitle = $dokument->titel;
$this->inline_css .= $dokument->css;

?>
<section class="well">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Stadtrecht<span class="caret"></span></a>
            <ul class="dropdown-menu" id="auswahl" style="padding: 5px 10px;">
                <li><input class="search" placeholder="Suche" style="width: 100%" /></li>
                <li><ul class="list" style="padding: 0;">
                    <?
                    /** @var Rechtsdokument[] $dokumente */
                    $dokumente = Rechtsdokument::model()->alle_sortiert();
                    foreach ($dokumente as $dok) {
                        echo '<li><span class="list-name">' . CHtml::link($dok->titel, Yii::app()->createUrl("infos/stadtrechtDokument", array("id" => $dok->id))) . '</span></li>' . "\n";
                    }
                    ?>
                </ul></li>
            </ul>
        </li>
        <li class="active"><?= $dokument->titel ?></li>
    </ul>

    <h1><?= CHtml::encode($dokument->titel)?> <span style="float: right"><a href="<?= $dokument->url_pdf ?>">als pdf</a></span></h1>
</section>


<div class="row" style="overflow:hidden;">
    <!--<div class="col col-md-3">
        <section class="well" style="margin-top: 50px;">
            <ul>
                <?
                /** @var Rechtsdokument[] $dokumente */
                $dokumente = Rechtsdokument::model()->findAll();
                foreach ($dokumente as $dok) {
                    echo '<li>' . CHtml::link($dok->titel, Yii::app()->createUrl("infos/stadtrechtDokument", array("id" => $dok->id))) . '</li>';
                }
                ?>
            </ul>
        </section>
    </div>-->
    <div class="col col-md-12">
        <section class="well rechtstext" style="margin-top: 50px;">
            <?= $dokument->html ?>
        </section>
    </div>
</div>

<script src="/js/list.js"></script>
<script>
var options = {
  valueNames: [ 'list-name' ]
};

var userList = new List('auswahl', options);
</script>
<script>
// http://stackoverflow.com/questions/10863821/bootstrap-dropdown-closing-when-clicked
$('.dropdown-menu input, .dropdown-menu label').click(function(e) {
    e.stopPropagation();
});
</script>
