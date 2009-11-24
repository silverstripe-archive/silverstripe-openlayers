/**
 * 
 */
var map = null;
var popup = null;		// global info-bubble for the map

var current_layer = null;
var wfsLayer = null;

var selectedFeature = null;

$(document).ready(function() {
	
	// reset the layerlist-form
	$('#layerlist')[0].reset();
	OpenLayers.ProxyHost="Proxy/dorequest?u=";

	// STYLE
	var myStyles = new OpenLayers.StyleMap({
        "default": new OpenLayers.Style({
            pointRadius: 5, // sized according to type attribute
            fillColor: "#FFD68F",
            strokeColor: "#ff9933",
            strokeWidth: 2,
			fillOpacity: 0.3
        }),
        "select": new OpenLayers.Style({
			pointRadius: 5, // sized according to type attribute
            fillColor: "#66ccff",
            strokeColor: "#3399ff",
			strokeWidth: 3
        })
    });
	
	// initialise map
	initMap('map');
	
	var controllerName = ss_config['Map']['PageName'];

	$(".change_visibility").click( setLayerVisibility );

	/**
	 * Initialise the open layers map instance and uses a div object which
	 * must exist in the DOM. 
	 *
	 * @param string divMap name of the target div object
	 **/
	function initMap(divMap) {

		map = new OpenLayers.Map(divMap, {
		    controls: [
		        new OpenLayers.Control.Navigation(),
		        new OpenLayers.Control.PanZoomBar(),
		   //     new OpenLayers.Control.LayerSwitcher({'ascending':false}),
		        new OpenLayers.Control.Permalink(),
		        new OpenLayers.Control.ScaleLine(),
		        new OpenLayers.Control.Permalink('permalink'),
		        new OpenLayers.Control.MousePosition(),
		        new OpenLayers.Control.OverviewMap(),
		        new OpenLayers.Control.KeyboardDefaults()
		    ],
		    numZoomLevels: 16
		});
		
		// initiate all overlay layers
		var layers = ss_config['Layer'];
		
		jQuery.each( layers , initLayer );
		
		//
		// enable all vector layers to be selectable via one controller.
		//
		// ASSUMPTION: all vector layers are queriable via WFS interface.
		var vectorLayers = map.getBy('layers','isVector',true);

		activateLayers(vectorLayers);

       // layer.events.on({"featureselected": onFeatureHighlighted});
		
		
		// set default location of the map
		var map_config = ss_config['Map'];
		var lon = map_config['Longitude'];
		var lat = map_config['Latitude'];
		var zoom = parseInt(map_config['DefaultZoom']);
		
		/*
		name = 'tilecache';
		options = {  transparent: 'false', layers: 'basic'};

		url = 'http://192.168.1.199/cgi-bin/tilecache.cgi';
		layer = new OpenLayers.Layer.WMS( name, url, options );
		map.addLayer(layer);
		*/	
		
		map.setCenter(new OpenLayers.LonLat(lon, lat), zoom);
		return;
	}
	
	/**
	 * Activates the map selector control to show the hover and popup features
	 * for WFS layers.
	 */
	function activateLayers( vectorLayers ) {

		// Create a select feature control and add it to the map.
		var highlightCtrl = new OpenLayers.Control.SelectFeature(vectorLayers, {
			hover: true,
			highlightOnly: true,
 			renderIntent: "temporary" 			
		});
		map.addControl(highlightCtrl);
		highlightCtrl.activate();

		var selectCtrl = new OpenLayers.Control.SelectFeature(vectorLayers, {
			onSelect: onFeatureSelect, 
			onUnselect: onFeatureUnselect}
		);
	   /*
			clickout: true,
			eventListeners: {
				featurehighlighted: onFeatureSelect
			}
		});
		*/
		map.addControl(selectCtrl);
		selectCtrl.activate();
	}
	
	
	
	/**
	 * Initiate a single layer by its layer-definitiion array. The array
	 * is generated via the CMS backend.
	 *
	 * @param int index Index of layer in the complate layer-array.
	 * @param array layerDef layer definition array.
	 */
	function initLayer( index, layerDef ) {	
		
		var layer = null;
		
		if (layerDef.Type == 'wms' || layerDef.Type == 'wmsUntiled') {
			var name = layerDef.Name;
			var url = layerDef.Url;
	 		var options = layerDef.Options;

			LayerType = 'wms';
			if(layerDef.Type == 'wmsUntiled'){
				layer = new OpenLayers.Layer.WMS.Untiled( name, url, options );
			} 
			else{
				layer = new OpenLayers.Layer.WMS( name, url, options );
			} 			
		} else
		if (layerDef.Type == 'wfs') {
			
			// get a WFS layer
			layer = createClusteredWFSLayer(layerDef);
		//	layer = createWFSLayer(layerDef);
			
			if(!wfsLayer) {
				wfsLayer = layer;	
			}
		} else 
		if (layerDef.Type == 'mapserver' || layerDev.Type == 'mapserverUntiled') {
			var url     = layerDef.Url;
			var name    = layerDef.Name;
	 		var options = layerDef.Options;
			var params  = layerDef.Params;
			layer = new OpenLayers.Layer.MapServer(name, url, options, params );
		}  

		// add new created layer to the map
		if (layer) {
			var visible = layerDef.Visible;
			
			layer.setVisibility(false);
			if (visible == "1") {
				layer.setVisibility(true);
			}
			map.addLayer(layer);

			if(current_layer == null && layerDef.ogc_transparent != 0) {
				current_layer =  layer;
			}
		}
	}


	/**
	 * Create a WFS layer instance for Open Layers.
	 */
	function createClusteredWFSLayer(layerDef) {

		
        var style_normal = new OpenLayers.Style({
            pointRadius: "3",
            fillColor: "#ffcc66",
            fillOpacity: 0.8,
            strokeColor: "#cc6633",
            strokeWidth: 2,
            strokeOpacity: 0.8
        });

        var style_select = new OpenLayers.Style({
            fillColor: "#66ccff",
            strokeColor: "#3399ff"
        });

				
        var style_clustered = new OpenLayers.Style({
            pointRadius: "${radius}",
            fillColor: "#ffcc66",
            fillOpacity: 0.8,
            strokeColor: "#cc6633",
            strokeWidth: 2,
            strokeOpacity: 0.8
        }, {
            context: {
                radius: function(feature) {
                    return Math.min(feature.attributes.count, 7) + 3;
                }
            }
        });

		var name    = layerDef.Name;
 		var options = layerDef.Options;
		var wfs_url = layerDef.Url+"?map="+options['map'];
		var featureType = layerDef.ogc_name;
		
		var p = new OpenLayers.Protocol.WFS({ 
				url: wfs_url,
				featureType: featureType,
				featurePrefix: null,
		});			
		p.format.setNamespace("feature", "http://mapserver.gis.umn.edu/mapserver");
		
		var s = new OpenLayers.StyleMap({
            "default": style_clustered,
            "select": {
                fillColor: "#8aeeef",
                strokeColor: "#32a8a9"
            },
			"temporary" : {
                fillColor: "#000000",
                strokeColor: "#f0f0f0"
			}
        });

        var strategyCluster = new OpenLayers.Strategy.Cluster();
		strategyCluster.distance = 25;
		
		strategies =  [
            new OpenLayers.Strategy.Fixed(),
        	strategyCluster
        ];

        layer = new OpenLayers.Layer.Vector(name, {
            strategies: strategies,
            protocol: p ,
            styleMap: s
        });

		return layer;
	}

	
	/**
	 * Create a WFS layer instance for Open Layers.
	 */
	function createWFSLayer(layerDef) {
		var wfs_url = layerDef.Url;
		var name    = layerDef.Name;
 		var options = layerDef.Options;

		layer = new OpenLayers.Layer.WFS(name, wfs_url, options);
		layer.styleMap = myStyles;
		
		return layer;
	}
	
	/**
	 * Set the visibility of a layer (callback from the Layer-List div object).
	 */
	function setLayerVisibility( event ){
		
		tempLayer = map.getLayersByName(this.value)[0];
		var status = tempLayer.getVisibility();
		
		if(status) {
			tempLayer.setVisibility(false);
		} else {
			tempLayer.setVisibility(true);
		}
	}
	
	/**
	 * Callback method, called when user clicks on a vector feature.
	 *
	 * @param the selected feature.
	 **/
	function onFeatureSelect( feature ){
		
		selectedFeature = feature;
        
		var info = 	'<img src=\'openlayers/images/ajax-loader.gif\' />&nbsp;loading information';
		

		// get event class
		pixel = this.handlers.feature.evt.xy;
		var pos = map.getLonLatFromViewPortPx(pixel);
		
		// remove existing popup
		if (popup != null) {
			popup.hide();
			map.removePopup(popup);
		}
				
		// create popup up with response //
		popup = new OpenLayers.Popup.FramedCloud(
			"popupinfo",
			new OpenLayers.LonLat(pos.lon,pos.lat),
			new OpenLayers.Size(200,200),
			info,
			null,
			true
		);
		
		feature.popup = popup;
		map.addPopup(popup);
		
		
		return;
		
		var fid = feature.feature.fid;
		
		// prepare request for AJAX 
		var url = controllerName + '/doGetFeatureInfo/'+ fid;
		
		// get attributes for selected feature (fid)
		OpenLayers.loadURL(url, null, this, onLoadPopup);
	}
	
    function onFeatureUnselect(feature) {
        map.removePopup(feature.popup);
        feature.popup.destroy();
        feature.popup = null;
    }	
	
	/**
	 * Shows the response of the AJAX call in the popup-bubble on the map if 
	 * available.
	 */
	function onLoadPopup(response) {
		innerHTML = response.responseText;
		
		if (popup != null) {
			popup.setContentHTML( innerHTML );
		}
	}
	
    function onPopupClose( evt ) {
        selectControl.unselect(selectedFeature);
    }
	
	
});
