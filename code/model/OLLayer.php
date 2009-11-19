<?php
/** 
 * http://202.36.29.39/cgi-bin/mapserv?map=%2Fsrv%2Fwww%2Fhtdocs%2Fmapdata%2Fspittelr%2Ftrack.map&layers=track&transparent=true&mode=map&map_imagetype=png&mapext=175.03692626953+-38.009948730469+175.82244873047+-37.224426269531&imgext=175.03692626953+-38.009948730469+175.82244873047+-37.224426269531&map_size=286+286&imgx=143&imgy=143&imgxy=286+286
 */
class OLLayer extends DataObject {
	
	static $db = array (
		"Name" 				=> "Varchar",
		"Url" 				=> "Varchar(1024)",
		"Type"			  	=> "Enum(array('wms','wfs','wmsUntiled','mapserver','mapserverUntiled'),'wms')",
		"DisplayPriority" 	=> "Int",
		"Enabled"         	=> "Boolean",
		"Visible"         	=> "Boolean",
		"Queryable"			=> "Boolean",
		
		// temporarily added (will be removed)
		"ogc_name"			=> "Varchar", 		// layer name (ogc layer name/id)
		"ogc_map"			=> "Varchar(1024)",	// url to the map file on the server side
		"ogc_transparent"  => "Boolean"			// transparent overlay layer
	);
	
	static $has_one = array(
		'MapPage' => 'OLMapPage'
	);	
	
	/**
	 * Creates and returns a layer definition array which will be used to configure
	 * open layers on the JavaScript side.
	 *
	 * @return array
	 */
	function serialise() {
		$result = $this->toMap();

		$options = array();
		$options['map']  = $this->getField("ogc_map");
		
		$layerType = $this->getField('Type');
		
		// handle layer type: WMS (tiled and untiled)
		if ($layerType == 'wms' || $layerType == 'wmsUntiled') {
			$options['layers']       = $this->getField("ogc_name");
			$options['transparent']  = $this->getField("ogc_transparent") ? "true": "false";
			$options['SSID'] = $this->getField('ID');
		} else 
		// handle layer type: WFS
		if ($layerType == 'wfs') {
			$options['typename']     = $this->getField("ogc_name");	
			$options['SSID'] = $this->getField('ID');		
		}
		// handle layer type: Mapserver (tiled and untiled)
		if ($layerType == 'mapserver' || $layerType == 'mapserverUntiled' ) {
			$options['layers']       = $this->getField("ogc_name");
			
			// we need to get this parameter from the backend. can be GIF, JPEG etc.
			$options['map_imagetype']= "png24";  
			$options['transparent']  ='true';
			
			// populate mapserver specific parameters into the js layer class
			// we need to get this parameter from the backend.
			$params = array();
			$params['isBaseLayer'] = false;
			$params['gutter']      = 15;
			$params['singleTile']  = ($layerType) ? true : false;

			$result['Params'] = $params;

		}
		$result['Options'] = $options;
		
		return $result;
	}
	
	function sendFeatureRequest($vars){
		
		$staticParams = array(
			'REQUEST' => 'GetFeatureInfo', 
			'INFO_FORMAT' => 'application/vnd.ogc.gml', 
			'VERSION' => '1.1.1', 
			'TRANSPARENT' => 'true', 
			'STYLE' => '', 
			'EXCEPTIONS' => 'application%2Fvnd.ogc.se_xml', 
			'FORMAT' => 'image%2Fpng',
			'SRS' => 'EPSG%3A4326'
		);
		//$vars = $data->getVars();
		$URLRequest = "?map=".$this->ogc_map."&";
		
		foreach($staticParams as $k => $v){
			
			$URLRequest .= $k.'='.$v.'&';
		}
		$URLRequest .= "LAYERS=".$this->ogc_name."&QUERY_LAYERS=".$this->ogc_name."&BBOX=".$vars['BBOX'];
		$URLRequest .= "&x=".$vars['x']."&y=".$vars['y']."&WIDTH=".$vars['WIDTH']."&HEIGHT=".$vars['HEIGHT'];
		$URLRequest = trim($URLRequest,"&");
		$URLRequest = str_replace('RequestURL=','',$URLRequest);
		
		$request = new RestfulService($this->Url);
		$xml = $request->request($URLRequest);
		return $xml;
	}
}