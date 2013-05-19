"use strict";
$.widget("openris.AntraegeKarte", {
	options: {
		lat: 48.155,
		lng: 11.55820,
		size: 11,
		benachrichtigungen_widget: false,
		benachrichtigungen_widget_zoom: 14,
		show_BAs: false,
		onSelect: null
	},
	map: null,
	markers: [],
	oms: null,

	_create: function () {
		var $widget = this,
			L_style = (typeof(window["devicePixelRatio"]) != "undefined" && window["devicePixelRatio"] > 1 ? "997@2x" : "997"),
			fullScreen = new L.Control.FullScreen();

		$widget.map = L.map($widget.element.attr("id"));
		$widget.map.setView([this.options["lat"], this.options["lng"]], this.options["size"]);

		$widget.map.addControl(fullScreen);
		L.tileLayer('/tiles/' + L_style + '/256/{z}/{x}/{y}.png', {
			attribution: '<a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a> | <a href="http://cloudmade.com">CloudMade</a>',
			maxZoom: 18,
			detectRetina: true
		}).addTo($widget.map);

		if (this.options["benachrichtigungen_widget"] !== false) this.initBenachrichtigungsWidget();
		if (this.options["show_BAs"]) this.initBAsWidget();
	},

	setAntraegeData: function (antraege_data) {
		var $widget = this,
			i;

		if ($widget.oms === null) {
			$widget.oms = new OverlappingMarkerSpiderfier($widget.map, {
				keepSpiderfied: true,
				nearbyDistance: 20
			});

			$widget.oms.addListener('click', function (marker) {
				marker.bindPopup(marker.desc);
				marker.openPopup();
			});
			$widget.oms.addListener('spiderfy', function (markers) {
				$widget.map.closePopup();
			});
		}

		for (i = 0; i < $widget.markers.length; i++) {
			$widget.map.removeLayer($widget.markers[i]);
		}

		$widget.markers = [];
		$widget.oms.clearMarkers();

		for (i = 0; i < antraege_data.length; i++) {
			// add a marker in the given location, attach some popup content to it and open the popup
			var markerIcon = L.AwesomeMarkers.icon({
				icon: 'heart',
				color: 'red'
			});
			var marker = new L.Marker([antraege_data[i][0], antraege_data[i][1]], {icon: markerIcon });
			marker.desc = antraege_data[i][2];
			$widget.map.addLayer(marker);
			$widget.oms.addMarker(marker);
			$widget.markers.push(marker);
		}

	},

	initBenachrichtigungsWidget: function () {
		var $widget = this,
			shown = false,
			drawnItems = new L.FeatureGroup(),
			$info = $("#" + $widget.options["benachrichtigungen_widget"]),
			editer = null;

		function ben_show() {
			if (shown) return;
			if ($info.length > 0) {
				$info.addClass("half_visible");
				window.setTimeout(function () {
					$info.addClass("half_visible_tmp1").removeClass("half_visible").addClass("visible");
				}, 200);
			}
			$widget.map.addLayer(drawnItems);
			shown = true;
		}

		function ben_hide() {
			if (!shown) return;
			if ($info.length > 0) {
				$info.removeClass("half_visible_tmp1").addClass("half_visible_tmp2").removeClass("visible").addClass("half_visible");
				window.setTimeout(function () {
					$info.removeClass("half_visible_tmp2").removeClass("half_visible");
				}, 300);
			}
			$widget.map.removeLayer(drawnItems);
			shown = false;
		}

		function ben_onZoom() {
			if ($widget.map.getZoom() >= $widget.options["benachrichtigungen_widget_zoom"]) {
				ben_show();
			} else {
				ben_hide();
			}
		}

		function ben_onClick(e) {
			if (editer !== null) return;

			var circle = L.circle(e.latlng, 500, {
					color: '#0000ff',
					fillColor: '#ff7800',
					fillOpacity: 0.4
				}),
				curr_rad = 500,
				curr_lat = e.latlng.lat,
				curr_lng = e.latlng.lng;

			drawnItems.addLayer(circle);

			editer = new L.EditToolbar.Edit($widget.map, {
				featureGroup: drawnItems,
				selectedPathOptions: {
					color: '#0000ff',
					fillColor: '#ff7800',
					fillOpacity: 0.4
				}
			});
			editer.enable();

			if (typeof($widget.options["onSelect"]) == "function") {
				circle.on('draw:edit edit', function () {
					var rad = circle.getRadius();
					var latlng = circle.getLatLng();
					if (rad == curr_rad && latlng.lat == curr_lat && latlng.lng == curr_lng) return;
					$widget.options["onSelect"](latlng, rad);
					curr_rad = rad;
					curr_lat = latlng.lat;
					curr_lng = latlng.lng;
				});
			}
			$widget.options["onSelect"](e.latlng, 500);
		}

		$widget.map.on("zoomend", ben_onZoom);
		$widget.map.on("click", ben_onClick);

		ben_onZoom();
	},

	initBAsWidget: function () {
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