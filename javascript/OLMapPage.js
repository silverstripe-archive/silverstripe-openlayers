/**
 * 
 */
var map = null;
var map_popup = null;		// global info-bubble for the map
var current_layer = null;
var xValue, yValue;			// not in use anymore ??
var wfsLayer = null;
var select;

$(document).ready(function() {
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
	
	map = new OpenLayers.Map('map', {
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

	/*
	name = 'tilecache';
	options = {  transparent: 'false', layers: 'basic'};
	
	url = 'http://192.168.1.199/cgi-bin/tilecache.cgi';
	layer = new OpenLayers.Layer.WMS( name, url, options );
	map.addLayer(layer);
	*/
	initMap(map);
	
	var controllerName = ss_config['Map']['PageName'];
	
	map.events.register('click', map, layerClick );
	$(".query_layer").click( clickQueryLayer );
	$(".change_visibility").click( layerVisibility );

	/**
	 * Initialise the open layers map instance.
	**/
	function initMap(map) {
		
		// initiate all overlay layers
		var layers = ss_config['Layer'];
		
		jQuery.each( layers , initLayer );
		
		// set default location of the map
		var map_config = ss_config['Map'];
		var lon = map_config['Longitude'];
		var lat = map_config['Latitude'];
		var zoom = parseInt(map_config['DefaultZoom']);

		if(wfsLayer){
			// Create a select feature control and add it to the map.
		    select = new OpenLayers.Control.SelectFeature(wfsLayer, {hover: true});
		    map.addControl(select);
		    select.activate();
		}
		
		map.setCenter(new OpenLayers.LonLat(lon, lat), zoom);
		return;
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
			var layer = null;
			LayerType = 'wms';
			if(layerDef.Type == 'wmsUntiled'){
				layer = new OpenLayers.Layer.WMS.Untiled( name, url, options );
				map.addLayer(layer);
			} 
			else{
				layer = new OpenLayers.Layer.WMS( name, url, options );
				map.addLayer(layer);
			} 			
		} else
		if (layerDef.Type == 'wfs') {
			var wfs_url = layerDef.Url;
			var name    = layerDef.Name;
	 		var options = layerDef.Options;
			layer = new OpenLayers.Layer.WFS(name, wfs_url, options);
			layer.styleMap = myStyles;
			if(!wfsLayer) wfsLayer = layer;	
			map.addLayer(layer);
			
		} else 
		if (layerDef.Type == 'mapserver' || layerDev.Type == 'mapserverUntiled') {
			var url     = layerDef.Url;
			var name    = layerDef.Name;
	 		var options = layerDef.Options;
			var params  = layerDef.Params;
			layer = new OpenLayers.Layer.MapServer(name, url, options, params );
			map.addLayer(layer);	
		}  
		if(current_layer == null && layerDef.ogc_transparent != 0) current_layer =  layer;
		
	}

	/** 
	 * Handle the click event on a layer to retrieve the attribute information.
	 */
	function layerClick( e ) {
		/*** NOT USING IT ANYMORE 
		alert('click');
		console.log(current_layer);
		pixel = new OpenLayers.Pixel(e.xy.x,e.xy.y);
		var pos = map.getLonLatFromViewPortPx(pixel);
		console.log(pos);
		
		var url = controllerName + '/doGetFeatureInfo'
		//var layer = current_layer;
		xValue = e.xy.x;
		yValue = e.xy.y;
	
		var param = new Array();
		param['x'] = e.xy.x;
		param['y'] = e.xy.y;
	
		param['SSID'] = current_layer.params.SSID;
		param['LAYERS'] = current_layer.name;
		param['BBOX'] = current_layer.map.getExtent().toBBOX();
		param['WIDTH'] = current_layer.map.size.w;
		param['HEIGHT'] = current_layer.map.size.h;
		
		pixel = new OpenLayers.Pixel(e.xy.x,e.xy.y);
		var pos = map.getLonLatFromViewPortPx(pixel);

		// remove existing popup
		if (map_popup != null) {
			map_popup.hide();
			map.removePopup(map_popup);
		}

		// create popup up with response 
		map_popup = new OpenLayers.Popup.FramedCloud(
			"popupinfo",
			new OpenLayers.LonLat(pos.lon,pos.lat),
			new OpenLayers.Size(200,200),
			'<img src=\'openlayers/images/ajax-loader.gif\' />&nbsp;loading information',
			null,
			true
		);
		map.addPopup(map_popup);
	
	    OpenLayers.loadURL(url, param, this, loadPopup);
	    OpenLayers.Event.stop(e);
		***/
	}
	
	/**
	 * Shows the response of the AJAX call in the popup-bubble on the map if 
	 * available.
	 */
	function loadPopup(response) {
		innerHTML = response.responseText;
		
		if (map_popup != null) {
			map_popup.setContentHTML( innerHTML );
		}
	}
	
	/**
	 * Currently not in use anymore :-(
	 */
	function openPopup(response){
		// transform Pixels to LonLat //
		px = new OpenLayers.Pixel(xValue,yValue);
		var pos = map.getLonLatFromViewPortPx(px);
		
		// create popup up with response //
		popup = new OpenLayers.Popup.FramedCloud(
			"popupinfo",
			new OpenLayers.LonLat(pos.lon,pos.lat),
			null,
			response.responseText,
			null,
			true
		);
		map.addPopup(popup);
	}
	
	/**
	 * Click event handler from Layers Menu
	 */
	function clickQueryLayer( event ) {
		
		// get new selected layer (selected via the radio buttons)
		layer = map.getLayersByName(this.value);
		
		// same layer -> nothing to do.
		if (layer == current_layer) {
			return;
		}else{
			
			map.events.unregister('click', current_layer, layerClick);
			map.events.register('click', layer, layerClick );
			current_layer = map.getLayersByName(this.value)[0];
			// Create a select feature control and add it to the map.
			select.destroy();
			if(current_layer.params.SERVICE == 'WFS'){
			    select = new OpenLayers.Control.SelectFeature(current_layer, {
					clickout: false, 
					toggle: false,
					multiple: false, 
					hover: false,
					eventListeners: {
						//beforefeaturehighlighted: destroyPopUp,
						featurehighlighted: createPopUp
						//click: createPopUp
					}
				
			});
			    map.addControl(select);
			    select.activate();
				current_layer.styleMap = myStyles;
			}
			
			return;
		}
		
		
	}
	
	function layerVisibility( event ){
		
		tempLayer = map.getLayersByName(this.value)[0];
		var status = tempLayer.getVisibility();
		if(status) tempLayer.setVisibility(false);
		else tempLayer.setVisibility(true);
		
	}
	
	/** function to create and show popup when hover an element on selected layer
	 ** @param e: 'selected' element.
	**/
	function createPopUp(e){
		//console.log(e);
		// transform Pixels to LonLat //
		px = new OpenLayers.Pixel(xValue,yValue);
		var pos = map.getLonLatFromViewPortPx(px);
		// create popup up with response //
		map_popup = new OpenLayers.Popup.FramedCloud(
			"popupinfo",
			new OpenLayers.LonLat(e.feature.geometry.x,e.feature.geometry.y),
			null,
			e.feature.fid,
			null,
			true
		);
		map.addPopup(map_popup);
		
	}
	
	/** function to destroy popup (if any)
	 ** 
	**/
	function destroyPopUp(){
		if(map_popup){
			map_popup.hide();
			map_popup.destroy();
			map_popup = null;
		}
	}
	
});


