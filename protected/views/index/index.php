<?php
/** @var $this IndexController */

$this->pageTitle = Yii::app()->name;

?>
	<style>
		#mapholder {
			margin-left: 10px;
			margin-right: 10px;
			margin-top: 30px;
			margin-bottom: 30px;
			border-radius: 10px;
			overflow: hidden;
			padding: 6px;
		}
		#map {
			height: 400px;
		}

		.leaflet-control-zoom-fullscreen {
			background-image: url(/js/leaflet.fullscreen/icon-fullscreen.png);
		}

		.leaflet-container:-webkit-full-screen {
			width: 100% !important;
			height: 100% !important;
		}

		.map-overlay-info {
			padding: 6px 8px;
			font: 14px/16px Arial, Helvetica, sans-serif;
			background: white;
			background: rgba(255,255,255,0.8);
			box-shadow: 0 0 15px rgba(0,0,0,0.2);
			border-radius: 5px;
		}
		.map-overlay-info h4 {
			margin: 0 0 5px;
			color: #777;
		}

	</style>

<div id="mapholder">
	<div id="map"></div>
</div>
<?
$BAfeatures = array();
/** @var array|Bezirksausschuss[] $BAs */
$BAs = Bezirksausschuss::model()->findAll();
foreach ($BAs as $ba) $BAfeatures[] = $ba->toGeoJSONArray();
?>
	<script>
		var map = L.map('map').setView([48.155, 11.55820], 11),
			L_style = (typeof(window.devicePixelRatio) != "undefined" && window.devicePixelRatio > 1 ? "997@2x" : "997"),
			fullScreen = new L.Control.FullScreen(),
			BAs = {"type":"FeatureCollection","features":<?=json_encode($BAfeatures)?>};
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
					mouseover: function(e) {
						if (geojson_clicked) return;
						geojson_show(e);
					},
					mouseout: function(e) {
						if (geojson_clicked) return;
						geojson_hide(e);
					},
					click: function(e) {
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


		// add a marker in the given location, attach some popup content to it and open the popup
		L.marker([48.13604, 11.55820]).addTo(map)
			.bindPopup('This is MUNICH!')
			.openPopup();
	</script>


<div class="row">
	<div class="col-span-4">
		<h2>Stadtratsanträge</h2>
		<ul>
		<?
		/** @var array|Antrag[] $antraege */
		$antraege = Antrag::model()->neueste_stadtratsantragsdokumente(96)->findAll();
		foreach ($antraege as $ant) {
			echo "<li>" . CHtml::link($ant->betreff, $ant->getLink()) . "<br>";
			foreach ($ant->dokumente as $dokument) {
				echo CHtml::encode($dokument->name) . ", ";
			}
			echo "</li>\n";
		}
		?>
		</ul>
	</div>
	<div class="col-span-4">
		<h2>Heading</h2>
		<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
		<p><a class="btn" href="#">View details &raquo;</a></p>
	</div>
	<div class="col-span-4">
		<h2>Heading</h2>
		<p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
		<p><a class="btn" href="#">View details &raquo;</a></p>
	</div>
</div>
