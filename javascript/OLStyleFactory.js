function OLStyleFactory() {
}

/**
 * Create a WFS layer instance for Open Layers.
 */
OLStyleFactory.createStyleMap = function (layerName) {
	
	var styleMap = null;

	if (layerName == 'DTIS') {
		styleMap = this.createStyleMap_DTIS();
	} else
	if (layerName == 'CTD') {
		styleMap = this.createStyleMap_CTD();
	} else {
		// default style
		styleMap = this.createStyleMap_Default();
	}
	return styleMap;
}

/**
 * Return a specific style map for CTD vector data
 */
OLStyleFactory.createStyleMap_Default = function() {
	
	default_style = new OpenLayers.Style({
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

	// create style map class
	var styleMap = new OpenLayers.StyleMap({
        "default": default_style,
        "select": {
            fillColor: "#8aeeef",
            strokeColor: "#32a8a9"
        },
		"temporary" : {
			fontColor: "#000000",
	        fillColor: "#ff6666",
            strokeColor: "#f00000"
		}
    });
	return styleMap;
}

/**
 * Return a specific style map for DTIS vector data
 */
OLStyleFactory.createStyleMap_DTIS = function() {
	
	default_style = new OpenLayers.Style({
		pointRadius: "${radius}",
		fillColor: "#ffcc66",
		fillOpacity: 0.8,
		strokeColor: "#cc6633",
		strokeWidth: 2,
		strokeOpacity: 0.8	,	       
		label: "${title}", 
		fontColor: "black"
    }, {
        context: {
            title: function(feature) {
                return (feature.attributes.count>1)? feature.attributes.count : '';
            },
            radius: function(feature) {
                return Math.min(feature.attributes.count, 7) + 5;
            }
        }
    });

	// create style map class
	var styleMap = new OpenLayers.StyleMap({
        "default": default_style,
        "select": {
            fillColor: "#8aeeef",
            strokeColor: "#32a8a9"
        },
		"temporary" : {
			fontColor: "#000000",
	        fillColor: "#ff6666",
            strokeColor: "#f00000"
		}
    });

	return styleMap;
}

/**
 * Return a specific style map for CTD vector data
 */
OLStyleFactory.createStyleMap_CTD = function() {
	
	default_style = new OpenLayers.Style({
			externalGraphic: "openlayers/javascript/jsparty/img/marker-green.png", 
	        pointRadius: "${radius}"
	    }, {
	        context: {
	            radius: function(feature) {
	                return Math.min(feature.attributes.count, 7) + 5;
	            }
	        }
    });

	// create style map class
	var styleMap = new OpenLayers.StyleMap({
        "default": default_style,
        "select": {
            fillColor: "#8aeeef",
            strokeColor: "#32a8a9"
        },
		"temporary" : {
			externalGraphic: "openlayers/javascript/jsparty/img/marker-gold.png", 
			fontColor: "#000000",
	        fillColor: "#ff6666",
            strokeColor: "#f00000"
		}
    });
	return styleMap;
}
