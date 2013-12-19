
function LayerFactory() {
}

/**
 * Create an OpenLayer Google Map layer object and returns its instance.
 * This method is called by createGoogleLayer.
 */
LayerFactory.prototype.createGoogleMapLayerObj = function(type) {
	var layer = null;
	
	if (type == 'Google Satellite' || type == 'Google Hybrid' || type == 'Google Physical') {
		if(type == 'Google Satellite') {
			layer = new OpenLayers.Layer.Google("Google Satellite", {type: G_SATELLITE_MAP});
		}
	
		if(type == 'Google Hybrid') {
			layer = new OpenLayers.Layer.Google("Google Hybrid", {type: G_HYBRID_MAP});
		}
	
		if(type == 'Google Physical') {
			layer = new OpenLayers.Layer.Google("Google Physical", {type: G_PHYSICAL_MAP});
		}
	}
	return layer;
}

/**
 * Create a clustered OpenLayer WFS layer object and returns its instance.
 * This method is called by createWFSLayer.
 */
LayerFactory.prototype.createClusteredWFSLayerObj = function(layerDef, styleMap) {
	
	var title      = layerDef.Title;
	var options    = layerDef.Options;	
	var baselayer  = layerDef.isBaseLayer;
	var url_params = '';
	var wfs_url    = layerDef.Url;
	var delimiter  = '?';
	
	if(typeof options['url_params'] !== 'undefined'){
		for(key in options['url_params']){
			if(options['url_params'][key] !== null) {
				url_params += key + "=" + options['url_params'][key] + "&";
			}
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
		styleMap: styleMap,
		isBaseLayer: baselayer,
		strategies: strategies,
		protocol: p 
	});
	return layer;
}

/**
 * Create an OpenLayer WFS layer object and returns its instance. 
 * This method is called by createWFSLayer.
 */
LayerFactory.prototype.createWFSLayerObj = function(layerDef, styleMap) {

	var title   = layerDef.Title;
	var options = layerDef.Options;	
	var baselayer = layerDef.isBaseLayer;
	
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
		styleMap: styleMap,
		strategies: strategies,
		isBaseLayer: baselayer,
		protocol: p 
	} );
	
	return layer;
}

/**
 * Factory method to create a WMS OpenLayer object and returns its instance.
 */
LayerFactory.prototype.getWMSLayer = function( layerDef ) {

	var layer = null;
	
	var title     = layerDef.Title;
	var url       = layerDef.Url;
	var options   = layerDef.Options;
	var baselayer = layerDef.isBaseLayer;
	
	if(layerDef.Type == 'wmsUntiled'){
		layer = new OpenLayers.Layer.WMS.Untiled( 
			title, url, options, 
			{wrapDateLine: true, isBaseLayer: baselayer } 
		);
	} 
	else{
		layer = new OpenLayers.Layer.WMS( 
			title, url, options,
			{wrapDateLine: true, isBaseLayer: baselayer, 	transitionEffect: 'resize' } 
		);
	}
	layer.wms_url = url;
	
	layer.setOpacity(parseFloat(layerDef.opacity));
	return layer;
}

LayerFactory.prototype.postGetWFSLayer = function(layer, layerDef, styleMap) {
}

/**
 * Factory method to create a WFS OpenLayer object and returns its instance.
 */
LayerFactory.prototype.getWFSLayer = function( layerDef ) {

	var layer = null;
	var styleMap = null;

	if (layerDef.StyleMapName != null) {
		styleMap = OLStyleFactory.getStyle(layerDef.StyleMapName);
	} else {
		styleMap = OLStyleFactory.createStyleMap(layerDef.ogc_name);
	}
	
	if (layerDef.Cluster == '1') {
		layer = this.createClusteredWFSLayerObj(layerDef, styleMap);
	} else {
		layer = this.createWFSLayerObj(layerDef, styleMap);
	}
	
	this.postGetWFSLayer(layer, layerDef, styleMap);

	return layer;
}

/**
 * Factory method to create a GoogleMaps layer object and returns its instance.
 */
LayerFactory.prototype.getGoogleLayer = function( layerDef ) {
	var layer = this.createGoogleMapLayerObj(layerDef.Type);	
	
	return layer;
}
