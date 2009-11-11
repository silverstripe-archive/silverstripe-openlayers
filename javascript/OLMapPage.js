/**
 * 
 */
var map = null;
var xValue, yValue;
$(document).ready(function() {

	OpenLayers.ProxyHost="Proxy/dorequest?u=";
	map = new OpenLayers.Map('map', {
	    controls: [
	        new OpenLayers.Control.Navigation(),
	        new OpenLayers.Control.PanZoomBar(),
	        new OpenLayers.Control.LayerSwitcher({'ascending':false}),
	        new OpenLayers.Control.Permalink(),
	        new OpenLayers.Control.ScaleLine(),
	        new OpenLayers.Control.Permalink('permalink'),
	        new OpenLayers.Control.MousePosition(),
	        new OpenLayers.Control.OverviewMap(),
	        new OpenLayers.Control.KeyboardDefaults()
	    ],
	    numZoomLevels: 16
	});
	var controllerName = ss_config['Map']['PageName'];
	initMap('map');
	
	/**
	 * Initialise the open layers map instance.
	**/
	function initMap(obj) {

		
	    
		//var infoControls;
		
		
		
		// initiate all overlay layers
		var layers = ss_config['Layer'];
		
		jQuery.each( layers , initLayer );
		
		// set default location of the map
		var map_config = ss_config['Map'];
		var lon = map_config['Longitude'];
		var lat = map_config['Latitude'];
		var zoom = map_config['DefaultZoom'];
		
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
			if(layerDef.Type == 'wmsUntiled'){
				var LayerType;
				LayerType = layerDef.Type;
				if(LayerType == 'wmsUntiled') LayerType = 'wms';
				layer = new OpenLayers.Layer.WMS.Untiled( name, url, options );
				map.addLayer(layer);
				
				map.events.register('click', map, function (e) {

					var url = controllerName + '/doGetFeatureInfo'
					
					xValue = e.xy.x;
					yValue = e.xy.y;
					
					var param = new Array();
					param['RequestURL'] = layer.url + '?map=' + layerDef.ogc_map;
					param['x'] = e.xy.x;
					param['y'] = e.xy.y;
					param['BBOX'] = layer.map.getExtent().toBBOX();
					param['QUERY_LAYERS'] = layer.params.LAYERS;
					param['LAYERS'] = layer.params.LAYERS;
					param['WIDTH'] = layer.map.size.w;
					param['HEIGHT'] = layer.map.size.h;
					param['SERVICE'] = LayerType;
					
					
				    OpenLayers.loadURL(url, param, this, openPopup);
				    OpenLayers.Event.stop(e);	   
				});
				
				/*
				map.events.register('click', map, function (e) {
				    var url =  layer.getFullRequestString({
				           REQUEST: "GetFeatureInfo",
				           EXCEPTIONS: "application/vnd.ogc.se_xml",
				           BBOX: layer.map.getExtent().toBBOX(),
				           X: e.xy.x,
				           Y: e.xy.y,
				           INFO_FORMAT: 'application/vnd.ogc.gml',
				           QUERY_LAYERS:layer.params.LAYERS,
				           WIDTH: layer.map.size.w,
				           HEIGHT: layer.map.size.h});
				           OpenLayers.loadURL(url, '', this, openPopup);
				           OpenLayers.Event.stop(e);
							
						   
				});
				*/
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
			map.addLayer(layer);
			
		} 
		
	}
	
	function openPopup(response){
		// transform Pixels to LonLat //
		px = new OpenLayers.Pixel(window.xValue,window.yValue);
		var lonlat = map.getLonLatFromViewPortPx(px);
		
		// create popup up with response //
		popup = new OpenLayers.Popup.FramedCloud(
			"popupinfo",
			new OpenLayers.LonLat(lonlat.lon,lonlat.lat),
			null,
			response.responseText,
			null,
			true
		);
		map.addPopup(popup);
	}
	
});


