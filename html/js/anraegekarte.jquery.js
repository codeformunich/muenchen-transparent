"use strict";
$.widget("openris.AntraegeKarte", {
	options: {
	},
	map: null,
	markers: [],

	_create: function () {
		var $widget = this,
			L_style = (typeof(window["devicePixelRatio"]) != "undefined" && window["devicePixelRatio"] > 1 ? "997@2x" : "997"),
			fullScreen = new L.Control.FullScreen(),
			BAs = {"type": "FeatureCollection", "features": BA_FEATURES},
			map = L.map('map').setView([48.155, 11.55820], 11);

		$widget.map = map;

		map.addControl(fullScreen);
		L.tileLayer('http://{s}.tile.cloudmade.com/2f8dd15a9aab49f9aa53f16ac3cb28cb/' + L_style + '/256/{z}/{x}/{y}.png', {
			attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
			maxZoom: 18,
			detectRetina: true
		}).addTo(map);


		var info = L.control();
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
		info.addTo(map);


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

		var geojson_clicked = false;


		var geojson = L.geoJson(BAs, {
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
		}).addTo(map);
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

	}
});