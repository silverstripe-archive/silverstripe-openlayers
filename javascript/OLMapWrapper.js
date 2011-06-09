var map = null;		// global map instance


/**
 * Initialise the open layers map instance and uses a div object which
 * must exist in the DOM. 
 *
 * @param string divMap name of the target div object
 **/
function initMap(divMap, mapConfig) {

	// set default location of the map
	var map_config = mapConfig['Map'];

	var lon  = parseFloat(map_config['Longitude']);
	var lat  = parseFloat(map_config['Latitude']);
	var zoom = parseInt(map_config['Zoom']);

	var map_resolutions = map_config['Resolutions'];
	var map_projection = map_config['Projection'];

	map = new OpenLayers.Map(divMap, {
		controls: [],
		resolutions: map_resolutions,
		projection: new OpenLayers.Projection(map_projection)
	});

	// get map extend (stored in the CMS)
	var map_extent = mapConfig['MaxMapExtent']
	var extent_left = parseFloat(map_extent['left']);
	var extent_bottom  = parseFloat(map_extent['bottom']);
	var extent_right = parseFloat(map_extent['right']);
	var extent_top = parseFloat(map_extent['top']);
	
	if (extent_left != 0 && extent_bottom != 0 && extent_right != 0 && extent_top != 0) {
		map.maxExtent = new OpenLayers.Bounds(-180, -90, 180, 90);
		map.restrictedExtent = new OpenLayers.Bounds(extent_left,extent_bottom,extent_right,extent_top);
	}
	
	map.paddingForPopups = new OpenLayers.Bounds(80, 20, 400, 60);
	
	// initiate all overlay layers
	var layers = mapConfig['Layers'];
	layers.reverse();
	jQuery.each( layers , initLayer );
	
	map.events.register("zoomend", map, onFeatureUnselect);
	map.zoomTo(zoom);
	
	map.setCenter(new OpenLayers.LonLat(lon, lat));
	
    map.addControl(new OpenLayers.Control.Navigation());
    map.addControl(new OpenLayers.Control.SSPanZoomBar());
    map.addControl(new OpenLayers.Control.ScaleLine());
    map.addControl(new OpenLayers.Control.KeyboardDefaults());
    map.addControl(new OpenLayers.Control.MousePosition());
		
	controls = map.getControlsByClass('OpenLayers.Control.Navigation');
	controls[0].handlers.wheel.activate();   
}

/**
 * Initiate a single layer by its layer-definitiion array. The array
 * is generated via the CMS backend.
 *
 * @param int index Index of layer in the complete layer-array.
 * @param array layerDef layer definition array.
 */
function initLayer( index, layerDef ) {	

	var layer = null;
	
	
	if (layerDef.Type == 'wms' || layerDef.Type == 'wmsUntiled') {
		var title = layerDef.Title;
		var url = layerDef.Url;
 		var options = layerDef.Options;

		LayerType = 'wms';
		if(layerDef.Type == 'wmsUntiled'){
			layer = new OpenLayers.Layer.WMS.Untiled( title, url, options, {wrapDateLine: true} );
		} 
		else{
			layer = new OpenLayers.Layer.WMS( title, url, options, {wrapDateLine: true} );
		} 			
		layer.wms_url = url;
	} else if (layerDef.Type == 'wfs') {

		// create WFS layer	
		if (layerDef.Cluster == '1') {
			layer = createClusteredWFSLayer(layerDef);
		} else {
			layer = createWFSLayer(layerDef);
		}
		var featureType = layerDef.ogc_name;
		
		var styleMap = null;
		if (layerDef.StyleMapName != null) {
			styleMap = OLStyleFactory.getStyle(layerDef.StyleMapName);
		} else {
			styleMap = OLStyleFactory.createStyleMap(featureType);
		}
		layer.styleMap = styleMap;
		
		
	}  
	
	
	// create a google map layer (it can be Google Physical, Google Hybrid, Google Satellite)
	// should we add google street?
	
	else if(layerDef.Type == 'Google Physical'){
		
		initGoogle('Google Physical');	
	} 
	else if(layerDef.Type == 'Google Hybrid'){
		
		initGoogle('Google Hybrid');	
	}
	else if(layerDef.Type == 'Google Satellite'){
		
		initGoogle('Google Satellite');	
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
	
	/** duplicate features for this layer (date line issue)
	 * @todo duplicate polygon layers (how?)
	 * @todo check if the map is configured to deal with date line?
	 */
	if(layer.features){
		
		var max_map_bounds = new OpenLayers.Bounds(-180,-90, 180, 90);
		layer.onFeatureInsert= function(feature) {

			if(!feature.isMirror){

				var featureMirror1 = new OpenLayers.Feature.Vector(
				new OpenLayers.Geometry.Point(
					(feature.geometry.x - max_map_bounds.getWidth()), feature.geometry.y),
					feature.attributes,
					feature.style);

				// copy feature attributes which are missing by default.
				featureMirror1.fid = feature.fid;

				featureMirror1.bounds = new OpenLayers.Bounds(featureMirror1.geometry.x,featureMirror1.geometry.y, featureMirror1.geometry.x,featureMirror1.geometry.y);

				if (feature.cluster != undefined) {
					featureMirror1.cluster = feature.cluster;
				}

				if (feature.data != undefined) {
					featureMirror1.data = feature.data;
				}
				featureMirror1.isMirror = true;

				feature.isMirror = false;
				this.addFeatures([featureMirror1]);
			}
		};
	}
}

function initGoogle(type){
	if(type == 'Google Satellite'){
		var layer = new OpenLayers.Layer.Google(
			"Google Satellite",
			{type: G_SATELLITE_MAP}
		);
	}
	
	if(type == 'Google Hybrid'){
		var layer = new OpenLayers.Layer.Google(
			"Google Hybrid",
			{type: G_HYBRID_MAP}
		);
	}
	
	if(type == 'Google Physical'){
		var layer = new OpenLayers.Layer.Google(
			"Google Physical",
			{type: G_PHYSICAL_MAP}
		);
	}
	
	map.addLayer(layer);
}

/**
 * Create a WFS layer instance for Open Layers.
 */
function createClusteredWFSLayer(layerDef) {
	
	var title   = layerDef.Title;
	var options = layerDef.Options;	
	var url_params = '';
	var wfs_url = layerDef.Url;
	var delimiter = '?';
	
	if(typeof options['url_params'] !== 'undefined'){
		for(key in options['url_params']){
			
			if(options['url_params'][key] !== null) url_params += key + "=" + options['url_params'][key] + "&";
		}
		url_params = url_params.substring(0, url_params.length-1);
	}

	var featureType = layerDef.ogc_name;

	var p = new OpenLayers.Protocol.WFS({ 
		url: wfs_url + "?" + url_params,
		featureType: featureType,
		featurePrefix: null
	});			
	
	// store the url into a separate parameter to have a backup in case we 
	// need to change the url (i.e for the species picklist).
	p.wfs_url = wfs_url + delimiter;
	p.format.setNamespace("feature", "http://mapserver.gis.umn.edu/mapserver");
	var strategyCluster = new OpenLayers.Strategy.Cluster();
	strategyCluster.distance = 25;

	strategies =  [
		new OpenLayers.Strategy.Fixed(),
		strategyCluster
	];

	layer = new OpenLayers.Layer.Vector(title, {
		strategies: strategies,
		protocol: p 
	});
	
	return layer;
}

/**
 * Create a WFS layer instance for Open Layers.
 */
function createWFSLayer(layerDef) {

	var title   = layerDef.Title;
	var options = layerDef.Options;	
	
	var wfs_url = layerDef.Url;
	var delimiter = '?';
	if (options['map'] != null) {
		wfs_url = layerDef.Url+delimiter+"map="+options['map'];
		delimiter = '&';
	} 
	var featureType = layerDef.ogc_name;

	var p = new OpenLayers.Protocol.WFS({ 
		url: wfs_url,
		featureType: featureType,
		featurePrefix: null
		
	});			
	
	// store the url into a separate parameter to have a backup in case we 
	// need to change the url (i.e for the species picklist).
	p.wfs_url = wfs_url + delimiter;
	p.format.setNamespace("feature", "http://mapserver.gis.umn.edu/mapserver");

	strategies =  [
		new OpenLayers.Strategy.Fixed()
	];

	layer = new OpenLayers.Layer.Vector(title, {
		strategies: strategies,
		protocol: p 
	});
	
	return layer;
}	
