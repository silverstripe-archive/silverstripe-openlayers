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
	$('input:checkbox:change_visibility').checkbox();
	$(".change_visibility").click( setLayerVisibility );
		
	$(".selectAllLayers").click( selectAllLayer );
	$(".unselectAllLayers").click( unselectAllLayer );
	$("a.multipleStations").click( multipleStationSelect );
	$(".closeButton").click( closeModalBox );
	
	$(".methodLink").click( openMethodPage );
	
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
		var handle = $("#innerLayers").sortable({ 
			cursor: 'move',
			opacity: 0.6,
			update: function(event, ui) { sortMapLayers(event , ui); }
		});
		$('#innerLayers').sortable('option', 'handle', '.sortableArea');
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
	var info = "";
	info = 	'<img src=\'openlayers/images/ajax-loader.gif\' /><strong>&nbsp;loading information, please wait...</strong>';
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
	pixel.y = pixel.y-68; //hack to always open popup above station  
	pixel.x = pixel.x-20;
	var pos = map.getLonLatFromViewPortPx(pixel);
	
	// remove existing popup
	if (popup != null) {
		popup.hide();
		map.removePopup(popup);
	}
			
	// create popup up with response //
	
	popup = new OpenLayers.SSPopup(
		"popupinfo",
		new OpenLayers.LonLat(pos.lon,pos.lat),
		new OpenLayers.Size(200,220),
		info,
		true,
		onFeatureUnselect
	);
	popup.panMapIfOutOfView = true;
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
		movePopup();
	}
}

/**
 * move popup when its content div dimensions change
**/
function movePopup(){
	// current popup content div height 
	var divHeight = $("#popupinfo_contentDiv").height();
	
	// get popup coordinates 
	var popPosition = map.getLayerPxFromLonLat(popup.lonlat);
	
	// we take 20px off because the spinning weel wont be there anymore
	divHeight = divHeight - 20;
	
	// move the popup divheight (new content) pixels up
	popPosition.y = popPosition.y - divHeight;
	
	// call function in popup
	popup.moveTo(popPosition);
	
	// pan map if necessary 
	popup.panIntoView();
}

/**
 * Close popup and unselect feature
**/
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
	$("input[class='change_visibility over']").attr('checked', true);
	$("input[class='change_visibility over']").each( function() {
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
	$("input[class='change_visibility over']").attr('checked', false);
	$("input[class='change_visibility over']").each( function() {
		
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

//----------------------------------------------------------------
//Modal Box
//----------------------------------------------------------------
var isCollapsed = 1;  //this variable is used to remember layer menu status 

function openStationPage( stationID, mapID ){
	if(isCollapsed == 1){
		collapse();
		isCollapsed = 0;
	}
	$("#locklayer").show();
	$("#modalbox").show();
	$("#modalbox .mbcontent").html("<img src='themes/niwa/images/modalLoader.gif'> Loading content, please wait...");
	$("#modalbox .mbcontent").load("atlasLoader/loadStation/" + stationID + "/" + mapID);
		
}

function openMethodPage(){
	collapse();
	isCollapsed = 0;
	var methodID = $(this).attr('id');
	$("#locklayer").show();
	$("#modalbox").show();
	$("#modalbox .mbcontent").html("<img src='themes/niwa/images/modalLoader.gif'> Loading content, please wait...");
	$("#modalbox .mbcontent").load("method/loadMethod/" + methodID);
}

function closeModalBox(){
	$("#locklayer").hide();
	$("#modalbox").hide();
	if(isCollapsed == 0){
		isCollapsed = 1;
		expand();
	} 
}

//--------------------------------------------------------------------------------
// MANAGE LAYERS MENU (OPEN/CLOSE IT AND REMEMBER STATUS WHEN OPENING MODAL BOX)
//--------------------------------------------------------------------------------

$('.panelTop .arrow').click(function(){
	if(isCollapsed == 0 || isCollapsed == 2){
		expand();
		isCollapsed = 1;
	}else{
		collapse();
		isCollapsed = 2;
	}
});

function collapse(){
	var w = -$('#mapPanel').width()-16;
	$('#mapPanel').animate({ right: w }, 500);
	$('.panelTop .arrow').addClass('layers');
}

function expand(){
	$('#mapPanel').animate({ right: 0 }, 500);
	$('.panelTop .arrow').removeClass('layers');
}

//----------------------------------------------------------------
// RESIZE LAYERS MENU HEIGHT
//----------------------------------------------------------------

$(window).resize(function(){resizeLayersPanel()});
var initH = $("#innerLayers").height();
function resizeLayersPanel(){
       
	var h = $(window).height()-($("#backgrounds").height()+320);
	if (h>initH){h = initH}
	$("#innerLayers").height(h);
}
resizeLayersPanel();

