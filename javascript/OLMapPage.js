/**
 * 
 */
var popup = null;		// global info-bubble for the map

var current_layer = null;
var selectedFeature = null;

// ---------------------------------------------------------------------------
$(document).ready(function() {
	
	// reset the layerlist-form
	$('#layerlist')[0].reset();	
		
	$(".change_visibility").change( setLayerVisibility );
		
	$(".selectAllLayers").click( selectAllLayer );
	$(".unselectAllLayers").click( unselectAllLayer );
	$("a.multipleStations").click( multipleStationSelect );
	OpenLayers.ProxyHost="Proxy/dorequest?u=";

	// initialise map
	initMap('map', ss_config);	
	
	//
	// enable all vector layers to be selectable via one controller.
	//
	// ASSUMPTION: all vector layers are queriable via WFS interface.
	var vectorLayers = map.getBy('layers','isVector',true);
	activateLayers(vectorLayers);
	
	// enable sort layers on layers panel
	$(function() {
		$("#innerLayers").sortable({ 
			cursor: 'move',
			opacity: 0.6,
			update: function(event, ui) { sortMapLayers(event , ui); }
		});
	});
	
});


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
	map.addControl(highlightCtrl);
	highlightCtrl.activate();
	
	map.addControl(selectCtrl);
	selectCtrl.activate();
}

/**
 * Returns the CMS page-name
 */
function getControllerName() {
	return ss_config['PageName'];
}

function multipleStationSelect(station){
	
	var mapid = ss_config['Map']['ID'];

	//var fid = feature.feature.fid;
	// prepare request for AJAX 
	var url = getControllerName() + '/dogetfeatureinfo/'+mapid+"/"+station;
	
	// get attributes for selected feature (fid)
	OpenLayers.loadURL(url, null, this, onLoadPopup);
	
}	
/**
 * Callback method, called when user clicks on a vector feature.
 *
 * @param the selected feature.
 **/
function onFeatureSelect( feature ){
	
	selectedFeature = feature;
	var info = 	'<img src=\'openlayers/images/ajax-loader.gif\' />&nbsp;loading information, please wait...';
	//var info = "You clicked on " + feature.layer.name;
	//info = info + "<br/>There are " + feature.attributes.count + " station in this point.<br/>";
	// get event class
	var clusterStations = new Array();
	var clusterStationsIDs = new Array();
	clusterStations = feature.cluster;
	if(clusterStations){
		for ( var i=0, len=clusterStations.length; i<len; ++i ){
			clusterStationsIDs.push(clusterStations[i].fid);
			//info = info + "<br/>" + clusterStations[i].fid;
		}
	} else{
		clusterStationsIDs.push(selectedFeature.fid);
	}
	
	pixel = this.handlers.feature.evt.xy;
	var pos = map.getLonLatFromViewPortPx(pixel);
	
	// remove existing popup
	if (popup != null) {
		popup.hide();
		map.removePopup(popup);
	}
			
	// create popup up with response //
	popup = new OpenLayers.Popup(
		"popupinfo",
		new OpenLayers.LonLat(pos.lon,pos.lat),
		new OpenLayers.Size(200,200),
		info,
		true,
		onFeatureUnselect
	);
	
	feature.popup = popup;
	map.addPopup(popup);

	var mapid = ss_config['Map']['ID'];

	//var fid = feature.feature.fid;
	// prepare request for AJAX 
	var url = getControllerName() + '/dogetfeatureinfo/'+mapid+"/"+clusterStationsIDs;
	
	// get attributes for selected feature (fid)
	OpenLayers.loadURL(url, null, this, onLoadPopup);
}

function onFeatureUnselect(feature) {
	if(popup !== null){
		map.removePopup(popup);
	    popup.destroy();
	    popup = null;
	}
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
 * Callback for click event 'Select all layers'
 */
function selectAllLayer(){
	$("input[type='checkbox']").attr('checked', true);
	$("input[type=checkbox]").each( function() {
		var name = $(this).attr('name');
		var tempLayer = map.getLayersByName(name);
		if(tempLayer.length > 0) {
			tempLayer[0].setVisibility(true);
		}
	});	
}

/**
 * Callback for click event 'Unselect all layers'
 */
function unselectAllLayer(){
	$("input[type='checkbox']").attr('checked', false);
	$("input[type=checkbox]").each( function() {
		var name = $(this).attr('name');
		var tempLayer = map.getLayersByName(name);
		if(tempLayer.length > 0) {
			tempLayer[0].setVisibility(false);
		}
	});
}

/**
 * callback for sort layers event (change map.layer.ZIndex)
**/
function sortMapLayers(event , ui){
	var maxZindex = 0;
	
	$("input[type=checkbox]").each( function() {
		var name = $(this).attr('name');
		var tempLayer = map.getLayersByName(name);
		if(tempLayer.length > 0) {
			if(maxZindex < map.getLayerIndex(tempLayer[0])) maxZindex = map.getLayerIndex(tempLayer[0]);
		}
	});
	
	$("input[type=checkbox]").each( function() {
		var name = $(this).attr('name');
		var tempLayer = map.getLayersByName(name);
		if(tempLayer.length > 0) {
			map.setLayerIndex(tempLayer[0],maxZindex);
			maxZindex = maxZindex - 1;
		}
	});
}

