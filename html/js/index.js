function index_aeltere_dokumente_load(url) {
	var $holder = $("#stadtratsdokumente_holder"),
		topc = $("#main_navbar").height() * -1;
	$holder.addClass("loading");
	$holder.prepend('<div class="loading_indicator"><span class="animate-spin icon-spin4"></span></div>');

	$holder.prepend('<div id="scroller" style="position: absolute; width: 1px; height: 1px; left: 0; top: ' + topc + 'px;"></div>');
	$("#scroller").scrollintoview();

	var done = false;
	$holder.parent().find("> .keine_dokumente").fadeOut(400, function() {
		$(this).hide();
		if (done) $holder.addClass("fullsize");
		else done = true;
	});

	$.getJSON(url, function(data) {
		if (done) $holder.addClass("fullsize");
		else done = true;
		$holder.html(data["html"]);
		$holder.removeClass("loading");
		$("#map").AntraegeKarte("setAntraegeData", data["geodata"]);
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

	$holder.addClass("loading");
	$holder.prepend('<div class="loading_indicator"><span class="animate-spin icon-spin4"></span></div>');

	$holder.prepend('<div id="scroller" style="position: absolute; width: 1px; height: 1px; left: 0; top: ' + topc + 'px;"></div>');
	//$("#scroller").scrollintoview();

	var done = false;
	$holder.parent().find("> .keine_dokumente").fadeOut(400, function() {
		$(this).hide();
		if (done) $holder.addClass("fullsize");
		else done = true;
	});

	$.getJSON(url, function(data) {
		if (done) $holder.addClass("fullsize");
		else done = true;
		$holder.html(data["html"]);
		$holder.removeClass("loading");
		$ben_holder.find(".zentrum_ort").text(data["naechster_ort"]);
		$ben_holder.find("input[name=krit_str]").val(data["krit_str"]);
		$("#map").AntraegeKarte("setAntraegeData", data["geodata"]);
	});

	return false;
}