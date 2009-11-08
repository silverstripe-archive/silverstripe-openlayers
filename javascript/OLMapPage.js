/**
 * 
 */
$(document).ready(function() {

	var map = null;

	OpenLayers.ProxyHost="Proxy/dorequest?u=";

	initMap('map');


	/**
	 * Initialise the open layers map instance.
	 */
	function initMap(obj) {
		map = new OpenLayers.Map(obj, {
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
		
		
		// Add contextual layer - get content from backend
		var contextual_layer = new OpenLayers.Layer.WMS( "Linz Topo",
		    "http://202.36.29.39/cgi-bin/mapserv?",
		    { map: '/srv/www/htdocs/mapdata/linz250.map',
		      transparent: 'false', layers: 'NZ250,NZ50_NI'}
		);
		contextual_layer.visibility = true;

		map.addLayer(contextual_layer);

		// initiate all overlay layers
		var layers = ss_config['Layer'];	
		jQuery.each( layers , initLayer );

		// set default location of the map
		var lon = 176.02294921875;
		var lat = -38.82568359375;
		var zoom = 6;

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
		
		if (layerDef.Type == 'wms') {
			var name = layerDef.Name;
			var url = layerDef.Url;
	 		var options = layerDef.Options;
	
	console.log(options);

			layer = new OpenLayers.Layer.WMS( name, url, options );
			map.addLayer(layer);
		} else
		if (layerDef.Type == 'wfs') {
			var wfs_url = layerDef.Url;
			var name    = layerDef.Name;
	 		var options = layerDef.Options;

			layer = new OpenLayers.Layer.WFS(name, wfs_url, options);
			map.addLayer(layer);
		} 
	}
	
});


