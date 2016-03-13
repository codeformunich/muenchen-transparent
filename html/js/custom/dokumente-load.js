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
        $benachrichtigung_hinweis = $("#benachrichtigung_hinweis_text");

    $benachrichtigung_hinweis.find(".nichts").hide();
    $benachrichtigung_hinweis.find(".infos").show();
    $(".benachrichtigung_add_geo").prop("disabled", false);

    $benachrichtigung_hinweis.find("input[name=geo_lng]").val(lng);
    $benachrichtigung_hinweis.find("input[name=geo_lat]").val(lat);
    $benachrichtigung_hinweis.find("input[name=geo_radius]").val(radius);

    $benachrichtigung_hinweis.find(".radius_m").text(parseInt(radius));

    if (url == "")
        return false;
    
    $holder.addClass("loading");
    $holder.prepend('<div class="loading_indicator"><span class="animate-spin icon-spin4"></span></div>');

    $holder.prepend('<div id="scroller" style="position: absolute; width: 1px; height: 1px; left: 0; top: ' + topc + 'px;"></div>');

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
        $benachrichtigung_hinweis.find(".zentrum_ort").text(data["naechster_ort"]);
        $benachrichtigung_hinweis.find("input[name=krit_str]").val(data["krit_str"]);
        $("#map").AntraegeKarte("setAntraegeData", data["geodata"], data["geodata_overflow"]);
    });

    return false;
}
