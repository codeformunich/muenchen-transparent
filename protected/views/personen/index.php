<?php
/**
 * @var InfosController $this
 * @var string $personen_typ
 * @var int|null $ba_nr
 * @var StadtraetIn[] $personen
 */

$bas = Bezirksausschuss::model()->alleOhneStadtrat();
$curr_ba = null;
if ($ba_nr > 0) foreach ($bas as $ba) if ($ba->ba_nr == $ba_nr) $curr_ba = $ba;

$personen_typ_name = ($personen_typ == 'str' ? 'Stadtratsmitglieder' : 'Mitglieder des Bezirksausschuss ' . $ba_nr . ' (' . $curr_ba->name . ')');
$this->pageTitle   = $personen_typ_name;

?>
<section class="well personen_liste">
    <ul class="breadcrumb" style="margin-bottom: 5px;">
        <li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
        <li class="active">Personen</li>
    </ul>

    <div class="row">
        <div class="col-sm-3 ba_selector">
            <div class="navbar-side">
                <nav class="navbar navbar-success">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                                data-target="#bs-example-navbar-collapse-1">
                            <span class="sr-only">BAs/StR anzeigen</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>

                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <?php
                            $str_link = Yii::app()->createUrl("personen/index");
                            if ($ba_nr === null) echo '<li class="stadtrat active"><a href="' . CHtml::encode($str_link) . '">Stadtrat <span class="sr-only">(aktuell)</span></a></li>';
                            else echo '<li class="stadtrat"><a href="' . CHtml::encode($str_link) . '">Stadtrat</a></li>';

                            foreach ($bas as $ba) {
                                $str_link = Yii::app()->createUrl("personen/index", array("ba" => $ba->ba_nr));
                                $name     = "BA " . $ba->ba_nr . " <small>(" . $ba->name . ")</small>";
                                if ($ba_nr === $ba->ba_nr) echo '<li class="active"><a href="' . CHtml::encode($str_link) . '">' . $name . ' <span class="sr-only">(aktuell)</span></a></li>';
                                else echo '<li><a href="' . CHtml::encode($str_link) . '">' . $name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <div class="col-sm-9">
            <?php
            //echo '<h2>' . CHtml::encode($personen_typ_name) . '</h2>';

            $fraktionen = array();
            foreach ($personen as $strIn) {
                if (count($strIn->stadtraetInnenFraktionen) > 0) {
                    $frakt = $strIn->stadtraetInnenFraktionen[0]->fraktion;
                } else {
                    $frakt = new Fraktion();
                    $frakt->id = "0";
                    $frakt->name = "Fraktionslos";
                }
                if (!isset($fraktionen[$frakt->id])) $fraktionen[$frakt->id] = $frakt->getName(true);
            }
            asort($fraktionen);

            ?>
            <div class="filter_sorter_holder<?php if (count($fraktionen) > 7) echo " extrabreit"; ?>">
                <div class="btn-group filter_widget" data-toggle="buttons">
                    <label class="btn btn-warning btn-separator-right active">
                        <input type="radio" name="options" value="" autocomplete="off" checked> Alle
                    </label>
                    <?php
                    foreach ($fraktionen as $fr_id => $fr_name) {
                        if ($fr_name == 'Die Grünen / RL') $fr_name = 'Grüne / RL';
                        if ($fr_name == 'Freiheitsrechte Transparenz Bürgerbeteiligung') $fr_name = 'Freiheitsrechte/...';
                        echo '<label class="btn btn-primary">';
                        echo '<input type="radio" name="options" value="' . $fr_id . '" autocomplete="off"> ' . CHtml::encode($fr_name);
                        echo '</label>';
                    }
                    ?>
                </div>

                <div class="sort_widget">
                    Sortierung:
                    <a href="#" data-sort="vorname" class="active">Vorname</a> &nbsp;
                    <a href="#" data-sort="nachname">Nachname</a>
                </div>
            </div>

            <ul class="strIn_liste">
                <?php
                $personen = StadtraetIn::sortByName($personen);
                foreach ($personen as $strIn) {
                    echo '<li class="strIn fraktion_';
                    if (count($strIn->stadtraetInnenFraktionen) > 0) {
                        echo $strIn->stadtraetInnenFraktionen[0]->fraktion_id;
                    } else {
                        echo "0";
                    }
                    echo ' "><div class="sm_links"></div>';
                    echo '<a href="' . CHtml::encode($strIn->getLink()) . '" class="name" data-vorname="' . CHtml::encode($strIn->errateVorname()) . '"';
                    echo ' data-nachname="' . CHtml::encode($strIn->errateNachname()) . '">' . CHtml::encode($strIn->getName()) . '</a>';
                    echo '<div class="partei">';
                    if (count($strIn->stadtraetInnenFraktionen) > 0) {
                        echo CHtml::encode($strIn->stadtraetInnenFraktionen[0]->fraktion->getName(true));
                    } else {
                        echo "Fraktionslos";
                    }
                    echo '</div>';
                    echo '</li>';
                }
                ?>
            </ul>

            <?php $this->load_isotope_js = true; ?>
            <script>
                $(function () {
                    var $liste = $(".strIn_liste"),
                        $filter = $(".filter_widget"),
                        $sorter = $(".sort_widget");
                    $liste.isotope({
                        itemSelector: '.strIn',
                        getSortData: {
                            partei: '.partei',
                            vorname: function (el) {
                                return $(el).find(".name").data("vorname");
                            },
                            nachname: function (el) {
                                return $(el).find(".name").data("nachname");
                            }
                        }
                    });
                    $filter.find("input").change(function () {
                        var val = $filter.find("input:checked").val();
                        if (val > 0 || val < 0 || val === "0")  $liste.isotope({filter: ".fraktion_" + val});
                        else if (val === "twitter" || val === "facebook" || val === "homepage") $liste.isotope({filter: "." + val});
                        else $liste.isotope({filter: null});
                    });
                    $sorter.find("a").click(function (ev) {
                        ev.preventDefault();
                        var val = $(this).data("sort");
                        $liste.isotope({sortBy: val});
                        $sorter.find("a").removeClass("active");
                        $(this).addClass("active");
                    });
                });
            </script>


        </div>
    </div>

</section>
