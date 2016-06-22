"use strict";

$.widget("muenchen-transparent.AntraegeKarte", {
    options: {
        lat: 48.15509285476017,
        lng: 11.542510986328125,
        size: 11,
        benachrichtigungen_widget: false,
        benachrichtigungen_widget_zoom: 14,
        show_BAs: false,
        ba_links: {},
        onSelect: null,
        outlineBA: 0,
         /** Anträge mit höchstens 10 Ortsbezügen */
        antraege_data: null,
        /** Anträge mit mehr als 10 Ortsbezügen */
        antraege_data_overflow: null
    },
    map: null,
    markers: [],
    oms: null,
    editer: null,
    circle: null,
    drawnItems: null,

    /** Baut die Karte mit allen leaflet-controls und den Markern */
    _create: function () {
        var $widget = this;

        // Die eigentliche Karte erstellen
        $widget.map = L.map($widget.element.attr("id"), {
            inertia: false
        });

        L.Icon.Default.imagePath = '/bower/leaflet/dist/images';

        var attrib = '<a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://opendatacommons.org/licenses/odbl/1.0/">ODbL</a> | <a href="http://developer.skobbler.com/" target="_blank">Scout</a>';

        if (typeof(window["devicePixelRatio"]) != "undefined" && window["devicePixelRatio"] > 1) attrib = '<a href="http://openstreetmap.org">OpenStreetMap</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a> | <a href="http://cloudmade.com">CloudMade</a>';

        window["map"] = $widget.map; /** @TODO Nur für debug-Zwecke da */

        var min_zoom = 11;
        var geojsonFeature = null;

        // Marker und View setzen
        if ($widget.options.outlineBA > 0) { // Anzeigen eines einzelnen BAs
            geojsonFeature = window["BA_GRENZEN_GEOJSON"][$widget.options.outlineBA - 1];

            // Finden eines Rechtecks, das den gesamten BA umfasst
            var array = geojsonFeature["geometry"]["coordinates"][0];
            var min_lon = Math.min.apply(Math, array.map( function(o) {return o[0];} )),
                max_lon = Math.max.apply(Math, array.map( function(o) {return o[0];} )),
                min_lat = Math.min.apply(Math, array.map( function(o) {return o[1];} )),
                max_lat = Math.max.apply(Math, array.map( function(o) {return o[1];} ));

            // Aus dem Mittelpunkt des Rechtecks wird der sichtbare Bereich bestimmt
            var center_lon = (min_lon + max_lon) / 2,
                center_lat = (min_lat + max_lat) / 2;

            $widget.map.setView([center_lat, center_lon], geojsonFeature["init_zoom"]);
        } else { // Anzeigen der ganzen Stadt
            geojsonFeature  = {
                "type": "Feature",
                "properties": {},
                "geometry": {
                    "type": "Polygon",
                    "coordinates": [MUC_GRENZEN_GEOJSON],
                }
            };
            $widget.map.setView([$widget.options["lat"], $widget.options["lng"]], $widget.options["size"]);
        }

        // Lässt Leaflet erkennen, was beim BA innen und was außen ist
        geojsonFeature["geometry"]["coordinates"].push([[11,47.6],[13,47.6],[13,48.5],[11,48.5]]);

        $widget.map.setMaxBounds([
            [48.4, 11.0],
            [47.9, 11.93]
        ]);

        // Adresssuche hinzufügen
        L.Control.geocoder({
            placeholder: "Suche nach Adresse...",
            errorMessage: "Nichts gefunden",
            collapsed: false,
            position: "topright"
        }).addTo($widget.map);

        // Automatische Positionsbestimmung
        L.control.locate({
            icon: 'glyphicon glyphicon-map-marker',
            iconLoading: 'glyphicon glyphicon-map-marker',
            strings: {
                title: "Wo bin ich?",
                popup: "Du bist innerhalb von {distance} {unit} um diesen Punkt",
                outsideMapBoundsMsg: "Du scheinst außerhalb dieser Karte zu sein"
            }
        }).addTo($widget.map);

        // Das eigentliche Kartenmaterial hinzufügen
        L.tileLayer('https://www.muenchen-transparent.de/tiles/' + (L.Browser.retina ? "512" : "256") + '/{z}/{x}/{y}.png', {
            "attribution": attrib,
            "maxZoom": 18,
            "minZoom": min_zoom
        }).addTo($widget.map);

        // Alles außerhalb Münchens augrauen
        var outside = L.geoJson(geojsonFeature).addTo($widget.map);
        outside.setStyle({
            weight: 0,
            fillColor: "#ffffff",
            fillOpacity: 0.75,
            stroke: false
        });

        if ($widget.options.benachrichtigungen_widget !== false) $widget.initBenachrichtigungsWidget();
        if ($widget.options.show_BAs) $widget.initBAsWidget();

        $("#overflow_hinweis").find("input").change(function() {
            $widget.rebuildMarkers($(this).prop("checked"));
        });

        if ($widget.options.antraege_data !== null) {
            $widget.setAntraegeData($widget.options.antraege_data, $widget.options.antraege_data_overflow);
        }
    },

    rebuildMarkers: function(mit_overflow, inline_marker_text) {
        var markers_pos_num, i, key,
            $widget = this,
            data = [];

        for (i = 0; i < $widget.markers.length; i++) {
            $widget.map.removeLayer($widget.markers[i]);
        }

        $widget.markers = [];
        $widget.oms.clearMarkers();

        for (i = 0; i < $widget.options.antraege_data.length; i++) data.push($widget.options.antraege_data[i]);
        if (mit_overflow) {
            for (i = 0; i < $widget.options.antraege_data_overflow.length; i++)
                for (var j in $widget.options.antraege_data_overflow[i])
                    data.push($widget.options.antraege_data_overflow[i][j]);
        }

        markers_pos_num = {};
        for (i = 0; i < data.length; i++) {
            key = data[i][0] + "_" + data[i][1];
            if (typeof(markers_pos_num[key]) != "undefined") markers_pos_num[key]++;
            else markers_pos_num[key] = 1;
        }

        for (i = 0; i < data.length; i++) {
            // add a marker in the given location, attach some popup content to it and open the popup
            var text_key = data[i][0] + "_" + data[i][1],
                multi_text = (markers_pos_num[text_key] > 1 ? markers_pos_num[text_key] : ""),
                markerIcon = L["TextMarkers"].icon({text: (inline_marker_text ? data[i][2] : multi_text) }),
                marker = new L.Marker([data[i][0], data[i][1]], {icon: markerIcon });
            marker.desc = data[i][2];
            $widget.map.addLayer(marker);
            $widget.oms.addMarker(marker);
            $widget.markers.push(marker);
        }

    },

    setAntraegeData: function (antraege_data, antraege_data_overflow) {
        var $widget = this,
            $overflow = $("#overflow_hinweis"),
            i;

        if ($widget.oms === null) {
            $widget.oms = new OverlappingMarkerSpiderfier($widget.map, {
                keepSpiderfied: true,
                nearbyDistance: 30
            });

            $widget.oms.addListener('click', function (marker) {
                if (typeof(marker._popup) == "undefined") {
                    marker.bindPopup(marker.desc).openPopup();
                }
            });
            $widget.oms.addListener('spiderfy', function (markers) {
                $widget.map.closePopup();
                for (i = 0; i < markers.length; i++) $(markers[i]._icon).find(".text").hide();
            });
            $widget.oms.addListener('unspiderfy', function (markers) {
                for (i = 0; i < markers.length; i++) $(markers[i]._icon).find(".text").show();
            });
        }

        $widget.options.antraege_data = antraege_data;
        $widget.options.antraege_data_overflow = antraege_data_overflow;

        $widget.rebuildMarkers($overflow.find("input").prop("checked"));

        if (antraege_data_overflow !== null && antraege_data_overflow.length > 0) {
            $overflow.css("display", "inline").find(".anzahl").text(antraege_data_overflow.length == 1 ? "1 Dokument" : antraege_data_overflow.length + " Dokumente");
        } else {
            $overflow.css("display", "hidden");
        }

    },

    // TODO: Wozu wird diese Funktion beötigt?
    /*setAntraegeDataTOPs: function(antraege_data) {
        var $widget = this,
            $overflow = $("#overflow_hinweis"),
            i;

        var curr_bound = $widget.map.getBounds(),
            visible_markers = [];
        for (i = 0; i < antraege_data.length; i++) {
            var item = antraege_data[i];
            if (curr_bound.contains(L.latLng(item[0], item[1]))) visible_markers.push(item);
        }

        if ($widget.oms === null) {
            $widget.oms = new OverlappingMarkerSpiderfier($widget.map, {
                keepSpiderfied: true,
                nearbyDistance: 20,
                keepSpiderfied_override: true
            });

            $widget.oms.addListener('click', function (marker) {
                if (typeof(marker._popup) == "undefined") {
                    marker.bindPopup(marker.desc).openPopup();
                }
            });
        }

        $widget.options.antraege_data = visible_markers;
        $widget.rebuildMarkers($overflow.find("input").prop("checked"), true);

        $widget.element.find(".leaflet-marker-textmarker").trigger("click");
        $widget.element.find(".leaflet-popup").hide();
    },*/

    /** Zeigt den Auswahlkreis und den Benachrichtungshinweis an oder entfernt ihn. */
    onZoomend: function () {
        var $widget = this;
        var info = $("#" + $widget.options.benachrichtigungen_widget);

        if ($widget.map.getZoom() >= $widget.options.benachrichtigungen_widget_zoom) { // Den Auswahlkreis zeigen
            if ($widget.editer !== null) return;
            if (info.length > 0) {
                info.addClass("half_visible");
                window.setTimeout(function () {
                    info.addClass("half_visible_tmp1").removeClass("half_visible").addClass("visible");
                }, 200);
            }
        } else { // Keinen Auswahlkreis zeigen
            if ($widget.editer === null) return;
            if (info.length > 0) {
                info.removeClass("half_visible_tmp1").addClass("half_visible_tmp2").removeClass("visible").addClass("half_visible");
                window.setTimeout(function () {
                    info.removeClass("half_visible_tmp2").removeClass("half_visible");
                }, 300);
            }

            $widget.editer.disable();
            $widget.editer = null;

            $widget.drawnItems.clearLayers();
            $widget.circle = null;
        }
    },

    /** Setzt den Auswahlkreis */
    onClick: function (e) {
        var $widget = this;

        if ($widget.map.getZoom() < $widget.options.benachrichtigungen_widget_zoom) return;
        if ($widget.editer !== null) return;

        $widget.circle = L.circle(e.latlng, 500, {
            color: '#0000ff',
            fillColor: '#ff7800',
            fillOpacity: 0.4
        });
        var curr_rad = 500,
            curr_lat = e.latlng.lat,
            curr_lng = e.latlng.lng;

        $widget.drawnItems.addLayer($widget.circle);

        $widget.editer = new L.EditToolbar.Edit($widget.map, {
            featureGroup: $widget.drawnItems,
            selectedPathOptions: {
                color: '#0000ff',
                fillColor: '#ff7800',
                fillOpacity: 0.4
            }
        });
        $widget.editer.enable();

        if (typeof($widget.options.onSelect) == "function") {
            $widget.circle.on('draw:edit edit', function () {
                var rad = $widget.circle.getRadius();
                var latlng = $widget.circle.getLatLng();
                if (rad == curr_rad && latlng.lat == curr_lat && latlng.lng == curr_lng) return;
                $widget.options.onSelect(latlng, rad, $widget.map.getZoom());
                curr_rad = rad;
                curr_lat = latlng.lat;
                curr_lng = latlng.lng;
            });
            $widget.options.onSelect(e.latlng, 500, $widget.map.getZoom());
        }
    },

    /** Setzt die Callbacks für zoom und click bzw. für den Auswahlkreis und den Benachrichtungshinweis */
    initBenachrichtigungsWidget: function () {
        var $widget = this;
        $widget.drawnItems = new L.FeatureGroup();
        $widget.map.addLayer($widget.drawnItems);

        $widget.map.on("zoomend", function()  { $widget.onZoomend(); } );
        $widget.map.on("click",   function(e) { $widget.onClick(e);  } );
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
                this._div = L.DomUtil.create('div', 'map-overlay-info');
                this.update();
                return this._div;
            };
            info.update = function (props) {
                if (props) {
                    this._div.innerHTML = '<h4>' + props["name"] + '</h4><!--<a href="' + props["website"] + '">Website</a>-->';
                    this._div.hidden = false;
                } else {
                    this._div.innerHTML = '';
                    this._div.hidden = true;
                }

            };
            $widget.map.addControl(info);
        }

        addInfo();

        var BAs = {"type": "FeatureCollection", "features": window["BA_GRENZEN_GEOJSON"]},
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
                            if (typeof($widget.options.ba_links["ba_" + e.target.feature.id]) == "undefined") return;
                            window.location.href = $widget.options.ba_links["ba_" + e.target.feature.id];
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
