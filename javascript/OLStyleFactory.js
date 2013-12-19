var styleMapList = new Array();

function OLStyleFactory() {

}

/**
 * Create a WFS layer instance for Open Layers.
 */
OLStyleFactory.addStyle = function (name, styleMap) {
	styleMapList[name] = styleMap;
}

/**
 * Return WFS layer instance for Open Layers.
 */
OLStyleFactory.getStyle = function(name) {
	return styleMapList[name];
}

/**
 * Create a WFS layer instance for Open Layers.
 */
OLStyleFactory.createStyleMap = function (layerName) {
	
	var styleMap = null;

	if (layerName == 'DTIS') {
		styleMap = this.createStyleMap_Point("#FFCC66", "#cc6633");
	} else
	if (layerName == 'CTD') {
		styleMap = this.createStyleMap_Point("#90ee90", "#60B060");
		// styleMap = this.createStyleMap_CTD();
	} else
	if (layerName == 'Bottom_grab') {
		styleMap = this.createStyleMap_Point("#CCFF66", "#606060");
	} else
	if (layerName == 'Multicorer') {
		styleMap = this.createStyleMap_Point("#CC66FF", "#606060");
	} else
	if (layerName == 'Sediment_sample') {
		styleMap = this.createStyleMap_Point("#FF66CC", "#606060");
	} else
	if (layerName == 'Beam_trawl') {
		styleMap = this.createStyleMap_Point("#00AAFF", "#606060");
	} else
	if (layerName == 'Brenke_sled') {
		styleMap = this.createStyleMap_Point("#88FFFF", "#606060");
	} else
	if (layerName == 'Dredge') {
		styleMap = this.createStyleMap_Point("#FFAAFF", "#606060");
	} else
	if (layerName == 'Epibenthic_sled') {
		styleMap = this.createStyleMap_Point("#55FF99", "#606060");
	} else {
		// default style
		styleMap = this.createStyleMap_Point("#FFCC66", "#cc6633");
	}
	return styleMap;
}

/**
 * Return a specific style map for CTD vector data
 */
OLStyleFactory.createStyleMap_Point = function(fillColor, strokeColor) {
	
	var default_style = new OpenLayers.Style({
        pointRadius: "${radius}",
        fillColor: fillColor,
        fillOpacity: 0.8,
        strokeColor: strokeColor,
        strokeWidth: 2,
        strokeOpacity: 0.8,
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
 * 
 */
OLStyleFactory.createStyleMap_Line = function(strokeColor,strokeWidth,strokeOpacity) {
	
	var default_style = new OpenLayers.Style({
        strokeColor: strokeColor,
        strokeWidth: strokeWidth,
        strokeOpacity: strokeOpacity
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
