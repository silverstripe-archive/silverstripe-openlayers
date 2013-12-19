
if (jQuery.browser.mozilla) {
	var setStyleWithoutFirefoxPatch = OpenLayers.Renderer.SVG.prototype.setStyle;
	OpenLayers.Renderer.SVG.prototype.setStyle = function(node, style, options) {
		var rv = setStyleWithoutFirefoxPatch.call(this, node, style, options);
	
		if (node && node.getAttributeNS) {
		   	var href = node.getAttributeNS(this.xlinkns, "href");
			if (href && href.charAt(0) == '#') {
		    	node.setAttributeNS(this.xlinkns, "href", window.location.href + href);
			}
		}
		return rv;
	}
}

var map = null;		// global map instance

/**
 * Initialise the open layers map instance and uses a div object which
 * must exist in the DOM. 
 *
 * @param string divMap name of the target div object
 **/
function initMap(divMap, mapConfig, layerFactory) {

	if (layerFactory === undefined) {
		layerFactory = new LayerFactory();
	}

	// set default location of the map
	var map_config = mapConfig['Map'];
	
	var lon  = parseFloat(map_config['Longitude']);
	var lat  = parseFloat(map_config['Latitude']);
	var zoom = parseInt(map_config['Zoom']);
	
	var map_resolutions = map_config['Resolutions'];
	var map_projection = map_config['Projection'];

	// map = new OpenLayers.Map(divMap);
	map = new OpenLayers.Map({
		div: divMap, 
		controls: [
			new OpenLayers.Control.Navigation(),
		    new OpenLayers.Control.SSPanZoomBar(),
		    new OpenLayers.Control.ScaleLine(),
		    new OpenLayers.Control.KeyboardDefaults(),
		    new OpenLayers.Control.MousePosition()
		],
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
	jQuery.each( layers , function(index, layer) {
		initLayer(layer, layerFactory);
	});
	
	// map.events.register("zoomend", map, onFeatureUnselect);
	
	var extent = null;
	if (map_config['DefaultExtent']) {
		var map_extent = map_config['DefaultExtent'];
		var extent_left = parseFloat(map_extent['left']);
		var extent_bottom  = parseFloat(map_extent['bottom']);
		var extent_right = parseFloat(map_extent['right']);
		var extent_top = parseFloat(map_extent['top']);
	
		if (extent_left != 0 && extent_bottom != 0 && extent_right != 0 && extent_top != 0) {
			extent = new OpenLayers.Bounds(extent_left,extent_bottom,extent_right,extent_top);
		}
	}
	
	if (extent) {
		map.zoomToExtent(extent);
	} else {
		map.setCenter(new OpenLayers.LonLat(lon, lat));
		map.zoomTo(zoom);
	}
	
	controls = map.getControlsByClass('OpenLayers.Control.Navigation');
	controls[0].handlers.wheel.activate();   
}

/**
 * Initiate a single layer by its layer-definitiion array. The array
 * is generated via the CMS backend.
 *
 * @param array layerDef layer definition array.
 * @param LayerFactory factoryclass to generate ol instances.
 */
function initLayer( layerDef, layerFactory) {	

	var layer = null;
	
	if (layerFactory === undefined) {
		layerFactory = new LayerFactory();
	}
	
	if (layerDef.Type == 'wms' || layerDef.Type == 'wmsUntiled') {
		layer = layerFactory.getWMSLayer(layerDef);
	} else 
	if (layerDef.Type == 'wfs') {
		layer = layerFactory.getWFSLayer(layerDef);
	} else 
	if (layerDef.Type == 'Google Physical' || layerDef.Type == 'Google Hybrid' || layerDef.Type == 'Google Satellite') {
		layer = layerFactory.getGoogleLayer(layerDef);
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
	if(layer.features) {
		
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


