"use strict";
$.widget("openris.AntraegeKarte", {
	options: {
		lat: 48.155,
		lng: 11.55820,
		size: 11,
		benachrichtigungen_widget: false,
		show_BAs: false
	},
	map: null,
	markers: [],

	_create: function () {
		var $widget = this,
			L_style = (typeof(window["devicePixelRatio"]) != "undefined" && window["devicePixelRatio"] > 1 ? "997@2x" : "997"),
			fullScreen = new L.Control.FullScreen();

		$widget.map = L.map('map');
		$widget.map.setView([this.options["lat"], this.options["lng"]], this.options["size"]);

		$widget.map.addControl(fullScreen);
		L.tileLayer('http://{s}.tile.cloudmade.com/2f8dd15a9aab49f9aa53f16ac3cb28cb/' + L_style + '/256/{z}/{x}/{y}.png', {
			attribution: '<a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a> | <a href="http://cloudmade.com">CloudMade</a>',
			maxZoom: 18,
			detectRetina: true
		}).addTo($widget.map);

		if (this.options["benachrichtigungen_widget"] !== false) this.initBenachrichtigungsWidget();
		if (this.options["show_BAs"]) this.showBAs();
	},

	setAntraegeData: function (antraege_data) {
		var $widget = this,
			i;
		for (i = 0; i < $widget.markers.length; i++) {
			$widget.map.removeLayer($widget.markers[i]);
		}
		$widget.markers = [];
		for (i = 0; i < antraege_data.length; i++) {
			// add a marker in the given location, attach some popup content to it and open the popup
			var marker = L.marker([antraege_data[i][0], antraege_data[i][1]]);
			marker.addTo($widget.map).bindPopup(antraege_data[i][2]) /*.openPopup() */;
			$widget.markers.push(marker);
		}

	},

	initBenachrichtigungsWidget: function()  {
		var $widget = this,
			shown = false;
		$widget.map.on("zoomend", function () {
			var $info = $("#" + $widget.options["benachrichtigungen_widget"]);
			if ($widget.map.getZoom() >= 14) {
				if (!shown) {
					$info.addClass("half_visible");
					window.setTimeout(function() {
						$info.addClass("half_visible_tmp1").removeClass("half_visible").addClass("visible");
					}, 200);
					shown = true;
				}
			} else {
				if (shown) {
					$info.removeClass("half_visible_tmp1").addClass("half_visible_tmp2").removeClass("visible").addClass("half_visible");
					window.setTimeout(function() {
						$info.removeClass("half_visible_tmp2").removeClass("half_visible");
					}, 300);
					shown = false;
				}
			}
		});
	},

	showBAs: function () {
		function geojson_show(e) {
			var layer = e.target;

			layer.setStyle({
				weight: 3,
				color: '#0000ff',
				dashArray: '',
				fillOpacity: 0.7
			});

			if (!L.Browser.ie && !L.Browser.opera) {
				layer.bringToFront();
			}

			info.update(layer.feature.properties);
		}

		function geojson_hide(e) {
			geojson.resetStyle(e.target);
			info.update();
		}

		var geojson_clicked = false,
			info,
			$widget = this;



		function addInfo() {
			info = L.control();
			info.onAdd = function (map) {
				this._div = L.DomUtil.create('div', 'map-overlay-info'); // create a div with a class "info"
				this.update();
				return this._div;
			};
			info.update = function (props) {
				if (props) {
					this._div.innerHTML = '<h4>' + props["name"] + '</h4><a href="' + props["website"] + '">Website</a>';
				} else {
					this._div.innerHTML = '';
				}

			};
			$widget.map.addControl(info);
		}
		addInfo();

		var BAs = {"type": "FeatureCollection", "features": BA_FEATURES},
			BA_map_options = {
				style: {
					fillColor: "#ff7800",
					weight: 1,
					opacity: 0.4,
					color: '#0000ff',
					dashArray: '2',
					fillOpacity: 0
				},
				onEachFeature: function (feature, layer) {
					layer.on({
						mouseover: function (e) {
							if (geojson_clicked) return;
							geojson_show(e);
						},
						mouseout: function (e) {
							if (geojson_clicked) return;
							geojson_hide(e);
						},
						click: function (e) {
							if (geojson_clicked) {
								geojson_clicked = false;
								geojson_hide(e);
							} else {
								geojson_clicked = true;
								geojson_show(e);
							}
						}
					});
				}
			},
			geojson = L.geoJson(BAs, BA_map_options).addTo($widget.map),
			active = true;

		$widget.map.on("zoomend", function () {
			if ($widget.map.getZoom() < 14) {
				if (!active) {
					geojson = L.geoJson(BAs, BA_map_options).addTo($widget.map);
					addInfo();
					active = true;
				}
			} else {
				if (active) {
					$widget.map.removeLayer(geojson);
					$widget.map.removeControl(info);
					active = false;
				}
			}
		});
	}
});