
alert('Deprecated code');
OpenLayers.ProxyHost="Proxy/dorequest?u=";

var map = new OpenLayers.Map('map', {
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


// Add contextual layers
var wms = new OpenLayers.Layer.WMS( "OpenLayers WMS", "http://labs.metacarta.com/wms/vmap0", {layers: 'basic'} );

var twms = new OpenLayers.Layer.WMS( "Linz Topo",
    "http://202.36.29.39/cgi-bin/mapserv?",
    { map: '/srv/www/htdocs/mapdata/linz250.map',
      transparent: 'false', layers: 'NZ250,NZ50_NI'}
);

twms.visibility = true;
//wms.visibility = false;

// Add background layer

var wms_background1 = new OpenLayers.Layer.WMS( "Contours",
    "http://202.36.29.39/cgi-bin/mapserv?",
    { map: '/srv/www/htdocs/mapdata/outer_contours.map',
      transparent: 'true', layers: 'CONTOUR_1,CONTOUR_0'}
);

// Add station WFS layer
wfs_url = "http://202.36.29.39/cgi-bin/mapserv?map=/srv/www/htdocs/mapdata/stations.map";

wfs_station1 = new OpenLayers.Layer.WFS("Beam trawl stations (WFS)", wfs_url, {'typename':'Beam_trawl'});
wfs_station2 = new OpenLayers.Layer.WFS("Dredge stations (WFS)", wfs_url, {'typename':'Dredge'});
wfs_station3 = new OpenLayers.Layer.WFS("Epibenthic sled stations (WFS)", wfs_url, {'typename':'Epibenthic_sled', visibility: false});
wfs_station4 = new OpenLayers.Layer.WFS("Brenke sled stations (WFS)", wfs_url, {'typename':'Brenke_sled', visibility: 'false'});
wfs_station5 = new OpenLayers.Layer.WFS("Sediment stations (WFS)", wfs_url, {'typename':'Sediment_sample', visibility: false});
wfs_station6 = new OpenLayers.Layer.WFS("Bottom grab stations (WFS)", wfs_url, {'typename':'Bottom_grab'});
wfs_station7 = new OpenLayers.Layer.WFS("Multicorer stations (WFS)", wfs_url, {'typename':'Multicorer'});
wfs_station8 = new OpenLayers.Layer.WFS("DTIS stations (WFS)", wfs_url, {'typename':'DTIS'});
wfs_station9 = new OpenLayers.Layer.WFS("CTD stations (WFS)", wfs_url, {'typename':'CTD'});

wms_url = "http://202.36.29.39/cgi-bin/mapserv?";
map_stations = "/srv/www/htdocs/mapdata/stations.map";

// Add station layers
var wms_station1 = new OpenLayers.Layer.WMS( "Beam trawl stations", wms_url, { map: map_stations, transparent: 'true', layers: 'Beam_trawl'});
var wms_station2 = new OpenLayers.Layer.WMS( "Dredge stations", wms_url, { map: map_stations, transparent: 'true', layers: 'Dredge'});
var wms_station3 = new OpenLayers.Layer.WMS( "Epibenthic sled stations", wms_url, { map: map_stations, transparent: 'true', layers: 'Epibenthic_sled'});
var wms_station4 = new OpenLayers.Layer.WMS( "Brenke sled stations", wms_url, { map: map_stations, transparent: 'true', layers: 'Brenke_sled'});
var wms_station5 = new OpenLayers.Layer.WMS( "Sediment stations", wms_url, { map: map_stations, transparent: 'true', layers: 'Sediment_sample'});
var wms_station6 = new OpenLayers.Layer.WMS( "Bottom grab stations", wms_url, { map: map_stations, transparent: 'true', layers: 'Bottom_grab'});
var wms_station7 = new OpenLayers.Layer.WMS( "Multicorer stations", wms_url, { map: map_stations, transparent: 'true', layers: 'Multicorer'});
var wms_station8 = new OpenLayers.Layer.WMS( "DTIS stations", wms_url, { map: map_stations, transparent: 'true', layers: 'DTIS'});

var wms_station9 = new OpenLayers.Layer.WMS( "CTD stations", wms_url, { map: map_stations,transparent: 'true', layers: 'CTD'});

wms_station1.visibility = false;
wms_station2.visibility = false;
wms_station3.visibility = false;
wms_station4.visibility = false;
wms_station5.visibility = false;
wms_station6.visibility = false;
wms_station7.visibility = false;
wms_station8.visibility = false;
wms_station9.visibility = false;

// Add Track layer
var wms_track1 = new OpenLayers.Layer.WMS( "Vessel Track",
    wms_url,
    { map: '/srv/www/htdocs/mapdata/track.map',
      transparent: 'true', layers: 'track'}
);
wms_track1.visibility = false;

// Add track WFS layer
wfs_url = "http://202.36.29.39/cgi-bin/mapserv?map=/srv/www/htdocs/mapdata/track.map";

rwfs = new OpenLayers.Layer.WFS("Vessel Track (WFS)", wfs_url, {'typename':'track'});

map.addLayers([wms, twms, wms_background1, 
	wms_track1,
	wms_station1, wms_station2, wms_station3, wms_station4, wms_station5, 
	wms_station6, wms_station7, wms_station8, wms_station9, rwfs,
	wfs_station1, wfs_station2, wfs_station3, wfs_station4, wfs_station5,
	wfs_station6, wfs_station7, wfs_station8, wfs_station9]);

var lon = 176.02294921875;
var lat = -38.82568359375;
var zoom = 6;


map.setCenter(new OpenLayers.LonLat(lon, lat), zoom);


function initmap(  ) {
	
	var os_layers = new array();
	
	var layer = SS_config['layers'];
	var os_layer

	if (layer.type == 'wfs') {
		os_layer = new OpenLayers.Layer.WFS(layer['name'], layer['url'], layer['options']);		
	
	} else 
	if (layer.type == 'wms') {
		os_layer = new OpenLayers.Layer.WFS(layer['name'], layer['url'], layer['options']);		
	}
	
	if (os_layer) {
		os_layer.visibility = layer['visible'];
		
//		os_layers[] = os_layer;
	}

}
