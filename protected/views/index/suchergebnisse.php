<?php
/**
 * @var IndexController $this
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 * @var RISSucheKrits $krits
 * @var bool $email_bestaetigt
 * @var bool $email_angegeben
 * @var bool $eingeloggt
 * @var bool $wird_benachrichtigt
 * @var BenutzerIn $ich
 * @var null|array $geodata
 * @var null|array $geodata_overflow
 */

$this->pageTitle = "Suchergebnisse";

?>

<script src="bower/Chart.js/Chart.min.js"></script>

<style>
.canvas-tooltip {
  list-style: none;
  margin-top: 10px;
}
.canvas-tooltip li {
  display: block;
  padding-left: 30px;
  position: relative;
  margin-bottom: 4px;
  border-radius: 5px;
  padding: 2px 8px 2px 28px;
  font-size: 14px;
  cursor: default;
  transition: background-color 200ms ease-in-out;
}
.canvas-tooltip li:hover {
  background-color: #fafafa;
}
.canvas-tooltip li span {
  display: block;
  position: absolute;
  left: 0;
  top: 0;
  width: 20px;
  height: 100%;
  border-radius: 5px;
}
</style>

<script>
$(function() {

var data = [
    {
        value: 0,
        color:"#F7464A",
        highlight: "#FF5A5E",
        label: "Termine"
    },
    {
        value: 0,
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "Antraege"
    },
    {
        value: 0,
        color: "#009933",
        highlight: "#009933",
        label: "Rathausumschau"
    },
    {
        value: 0,
        color: "#FDB45C",
        highlight: "#FFC870",
        label: "Andere"
    }
]

for (current of $(".suchergebnisse > ul h4 > a").toArray()) {
    var link = $(current).attr("href");
    if (link.indexOf("/termine/") > -1) {
        data[0].value += 1;
    } else if (link.indexOf("/antraege/") > -1) {
        data[1].value += 1;
    } else if (link.indexOf("Rathaus-Umschau") > -1) {
        data[2].value += 1;
    } else {
        data[3].value += 1;
    }
};

var chart = $("#dokumentenzugehoerigkeit").get(0).getContext("2d");
var dokumentenzugehoerigkeit = new Chart(chart).Pie(data, {responsive: false, animation: false, tooltipTemplate : "<%if (label){%><%=label%>: <%}%><%= value %>"});
$(".canvas-tooltip").html(dokumentenzugehoerigkeit.generateLegend());

});
</script>

<section>
    <h3>Dokumentenzugehörigkeit</h3>
    <div style="overflow: hidden;">
        <canvas id="dokumentenzugehoerigkeit" style="float: left" height="200" width="200"></canvas>
        <div class="canvas-tooltip" style="float: left"><div>
    </div>
</section>

<script>
$(function(){

var data = {
    labels: ["January", "February", "March",
             "April", "May", "June",
             "July", "August", "September",
             "October","November", "December"],
    datasets: [
        {
            label: "My Second dataset",
            fillColor: "rgba(151,187,205,0.2)",
            strokeColor: "rgba(151,187,205,1)",
            pointColor: "rgba(151,187,205,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(151,187,205,1)",
            data: [0, 0, 0,
                   0, 0, 0,
                   0, 0, 0,
                   0, 0, 0]
        }
    ]
};


for (current of $(".suchergebnisse > ul h4 span.least-content").toArray()) {
    var month = parseInt($(current).text().split('.')[1]);
    if (month - 1 < 12) {
        data.datasets[0].data[month - 1] += 1;
    } else {
        console.log("Illegal month found: " + month);
    }
};

console.log(data.datasets[0].data);

var chart = $("#nach_datum").get(0).getContext("2d");
var nachdatum = new Chart(chart).Line(data, {responsive: true, animation: false});

});
</script>

<section>
    <h3>Dokumente nach Datum der letzten Änderung des Vorgangs</h3>
    <canvas id="nach_datum" style="width: 100%; max-height: 200px"></canvas>
</section>

<section class="well suchergebnisse">
    <div class="pull-right" style="text-align: center;">
        <?
        $this->renderPartial("suchergebnisse_benachrichtigungen", array(
            "eingeloggt"          => $eingeloggt,
            "email_angegeben"     => $email_angegeben,
            "email_bestaetigt"    => $email_bestaetigt,
            "wird_benachrichtigt" => $wird_benachrichtigt,
            "ich"                 => $ich,
            "krits"               => $krits
        ));
        ?>
        <div style="font-size: 90%;">
            <a href="<?= CHtml::encode($krits->getFeedUrl()) ?>"><span class="fontello-rss"></span> Suchergebnisse als RSS-Feed</a>
        </div>
    </div>

    <?
    if ($krits->getKritsCount() == 1) {
        echo '<h1>' . CHtml::encode($krits->getTitle()) . '</h1>';
    } else {
        echo '<h1>Suchergebnisse</h1>';
        echo '<div class="suchkrit_beschreibung">';
        echo CHtml::encode($krits->getTitle());
        echo '</div>';
    }
    echo '<br style="clear: both;">';

    if (!is_null($geodata) && count($geodata) > 0) {
        $geokrit     = $krits->getGeoKrit();
        ?>
        <div id="mapholder">
            <div id="map"></div>
        </div>
        <div id="overflow_hinweis" <? if (count($geodata_overflow) == 0) echo "style='display: none;'"; ?>><label><input type="checkbox" name="zeige_overflow"> Zeige <span
                    class="anzahl"><?= (count($geodata_overflow) == 1 ? "1 Dokument" : count($geodata_overflow) . " Dokumente") ?></span> mit über 20 Ortsbezügen</label></div>

        <script>
            $(function () {
                var $map = $("#map").AntraegeKarte({
                    lat: <?=$geokrit["lat"]?>,
                    lng: <?=$geokrit["lng"]?>,
                    size: 14,
                    antraege_data: <?=json_encode($geodata)?>,
                    antraege_data_overflow: <?=json_encode($geodata_overflow)?>,
                });
            });
        </script>
    <?
    }

    $facet_groups = array();

    $antrag_typ = array();
    $facet      = $ergebnisse->getFacetSet()->getFacet('antrag_typ');
    foreach ($facet as $value => $count) if ($count > 0) {
        $str = "<li><a href='" . RISTools::bracketEscape(CHtml::encode($krits->cloneKrits()->addAntragTypKrit($value)->getUrl())) . "'>";
        if (isset(Antrag::$TYPEN_ALLE[$value])) {
            $x = explode("|", Antrag::$TYPEN_ALLE[$value]);
            $str .= $x[1] . ' (' . $count . ')';
        } elseif ($value == "stadtrat_termin") $str .= 'Stadtrats-Termin (' . $count . ')';
        elseif ($value == "ba_termin") $str .= 'BA-Termin (' . $count . ')';
        else $str .= $value . " (" . $count . ")";
        $str .= "</a></li>";
        $antrag_typ[] = $str;
    }
    if (count($antrag_typ) > 0) $facet_groups["Dokumenttypen"] = $antrag_typ;

    $wahlperiode = array();
    $facet       = $ergebnisse->getFacetSet()->getFacet('antrag_wahlperiode');
    foreach ($facet as $value => $count) if ($count > 0) {
        if (in_array($value, array("", "?"))) continue;
        $str = "<li><a href='" . RISTools::bracketEscape(CHtml::encode($krits->cloneKrits()->addWahlperiodeKrit($value)->getUrl())) . "'>";
        $str .= $value . ' (' . $count . ')';
        $str .= "</a></li>";
        $wahlperiode[] = $str;
    }
    if (count($wahlperiode) > 0) $facet_groups["Wahlperiode"] = $wahlperiode;

    $has_facets = false;
    foreach ($facet_groups as $name => $facets) if (count($facets) > 1) $has_facets = true;
    if ($has_facets) {
        ?>
        <section class="suchergebnis_eingrenzen">
            <div class="opener"><a href="#suchergebnis_eingrenzen_holder" onclick="$(this).parents('.suchergebnis_eingrenzen').toggleClass('visible'); return false;">
                    <span class="glyphicon glyphicon-chevron-down open_icon"></span>
                    <span class="glyphicon glyphicon-chevron-up close_icon"></span>
                    Suchergebnisse einschränken
                </a></div>
            <div id="suchergebnis_eingrenzen_holder">
                <?
                foreach ($facet_groups as $name => $facets) if (count($facets) > 1) {
                    echo '<div class="eingrenzen_row"><h3>' . CHtml::encode($name) . '</h3><ul>';
                    echo implode("", $facets);
                    echo '</ul></div>';
                }
                ?>
            </div>
        </section>
    <? }

    echo '<br style="clear: both;">';

    if ($krits->getKritsCount() > 0) $this->renderPartial("../benachrichtigungen/suchergebnisse_liste", array(
        "ergebnisse" => $ergebnisse,
    ));
    ?>
</section>
