function index_datum_dokumente_load(node, url_ajax) {
    var $holder = $("#stadtratsdokumente_holder"),
        topc = $("#main_navbar").height() * -1,
        url_std = $(node).attr("href");
    $holder.addClass("loading");
    $holder.prepend('<div class="loading_indicator"><span class="animate-spin icon-spin4"></span></div>');

    $holder.prepend('<div id="scroller" style="position: absolute; width: 1px; height: 1px; left: 0; top: ' + topc + 'px;"></div>');
    $("#scroller").scrollintoview();

    $("#listen_holder").addClass("nur_dokumente");

    if ($("html").hasClass("history")) {
        window.history.pushState(null, null, url_std);
    }

    $.getJSON(url_ajax, function (data) {
        $holder.html(data["html"]);
        $holder.removeClass("loading");
        $("#map").AntraegeKarte("setAntraegeData", data["geodata"], data["geodata_overflow"]);
    });

    return false;
}


function index_geo_dokumente_load(url, lng, lat, radius) {
    var $holder = $("#stadtratsdokumente_holder"),
        topc = $("#main_navbar").height() * -1,
        $ben_holder = $("#ben_map_infos");

    $ben_holder.find(".nichts").hide();
    $ben_holder.find(".infos").show();
    $(".ben_add_geo").prop("disabled", false);

    $ben_holder.find("input[name=geo_lng]").val(lng);
    $ben_holder.find("input[name=geo_lat]").val(lat);
    $ben_holder.find("input[name=geo_radius]").val(radius);

    $ben_holder.find(".radius_m").text(parseInt(radius));

    if (url != "") {
        $holder.addClass("loading");
        $holder.prepend('<div class="loading_indicator"><span class="animate-spin icon-spin4"></span></div>');

        $holder.prepend('<div id="scroller" style="position: absolute; width: 1px; height: 1px; left: 0; top: ' + topc + 'px;"></div>');
        //$("#scroller").scrollintoview();

        var done = false;
        $holder.parent().find("> .keine_dokumente").fadeOut(400, function () {
            $(this).hide();
            if (done) $holder.addClass("fullsize");
            else done = true;
        });

        $.getJSON(url, function (data) {
            if (done) $holder.addClass("fullsize");
            else done = true;
            $holder.html(data["html"]);
            $holder.removeClass("loading");
            $ben_holder.find(".zentrum_ort").text(data["naechster_ort"]);
            $ben_holder.find("input[name=krit_str]").val(data["krit_str"]);
            $("#map").AntraegeKarte("setAntraegeData", data["geodata"], data["geodata_overflow"]);
        });
    }

    return false;
}


$(function () {
    var $quicksearch = $("#quicksearch_form").find("input[type=text]");

    var mitglieder = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: $quicksearch.data("prefetch-url")
    });
    var ba_list = [];
    $("#ba_nav_list").find("a").each(function() {
        ba_list.push({
            "value": $(this).text(),
            "url": $(this).attr("href")
        });
    });
    var bas = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        local: ba_list
    });

    mitglieder.initialize();
    bas.initialize();

    $quicksearch.typeahead({
            hint: false,
            highlight: true,
            minLength: 1
        },
        {
            name: 'volltext',
            displayKey: 'value',
            source: function findMatches(q, cb) {
                cb([{
                    value: q,
                    url: $quicksearch.data("search-url").replace(/SUCHBEGRIFF/, encodeURI(q))
                }])
            },
            templates: {
                header: '<h3 class="quicksearch-cat">Volltextsuche nach:</h3>'
            }
        },
        {
            name: 'states',
            displayKey: 'value',
            source: bas.ttAdapter(),
            templates: {
                header: '<h3 class="quicksearch-cat">Bezirksausschuss</h3>'
            }
        },
        {
            name: 'mitglieder',
            displayKey: 'value',
            source: mitglieder.ttAdapter(),
            templates: {
                header: '<h3 class="quicksearch-cat">Stadtrats-/BA-Mitglieder</h3>'
            }
        });
    $quicksearch.on("typeahead:selected", function (ev, obj) {
        if (typeof(obj.url) != "undefined") window.location.href = obj.url;
    });

    if (!Modernizr.testAllProps("hyphens")) yepnope.injectJs("/js/hyphenator.js");
});