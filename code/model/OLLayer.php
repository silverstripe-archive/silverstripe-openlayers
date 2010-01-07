<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage code
 */


/** 
 * Layer class. Each instance of this layer class represents a JavaScript
 * OpenLayer Layer class. It is used to manage and control the map 
 * behaviour.
 */
class OLLayer extends DataObject {
	
	public static $singular_name = 'Layer';
	
	public static $plural_name = 'Layers';	
	
	static $db = array (
		"Title"				=> "Varchar(50)",
		"Url" 				=> "Varchar(1024)",
		"LayerType"		  	=> "Enum(array('overlay','background','contextual'),'overlay')",
		"Type"			  	=> "Enum(array('wms','wfs','wmsUntiled'),'wms')",
		"DisplayPriority" 	=> "Int",		
		"Enabled"         	=> "Boolean",
		"Visible"         	=> "Boolean",
		"Queryable"			=> "Boolean",
		
		"GeometryType"		=> "Enum(array('Point','Polygon','Line','Raster'),'Point')",
		"Cluster"			=> "Boolean",
		"XMLWhitelist"		=> "Varchar(255)",
		
		// temporarily added (will be removed)
		"ogc_name"			=> "Varchar(100)",		// layer name (ogc layer name/id)
		"ogc_map"			=> "Varchar(1024)",		// url to the map file on the server side
		"ogc_format"		=> "Enum(array('png','jpeg','png24','gif'),'png')",
		"ogc_transparent"	=> "Boolean"			// transparent overlay layer
	);
	
	static $has_one = array(
		'Map' => 'OLMapObject'
	);	
	
	static $field_labels = array(
		"Type"             => "OGC API",
		"ogc_name"         => "OGC Layer Name",
		"ogc_map"          => "Map-filename",
		"ogc_format"       => "Image Format",
		"ogc_transparent"  => "Transparency",
		"Map.Title"        => "Map Name",
		"XMLWhitelist"     => "Get Feature XML Whitelist (comma separated)"
	);	
	
	static $summary_fields = array(
		'Title',
		'ogc_name',
		'Type',
		'GeometryType',
		'Cluster',
		'Enabled',
		'Visible',
		'Queryable',
		'ogc_transparent',
		'Map.Title'
	 );

	static $defaults = array(
	    'DisplayPriority' => 50,
	    'Enabled' => true,
	    'Visible' => false,
	    'Queryable' => true,
	    'ogc_transparent' => true,
		'GeometryType' => 'Point',
		'LayerType' => 'overlay', 
		'XMLWhitelist' => 'attribute'
	 );

	static $casting = array(
		'Enabled' => 'Boolean',
	);
	
	static $default_sort = "Title ASC";

	/**
	 * Overwrites SiteTree.getCMSFields.
	 *
	 * @return fieldset
	 */ 
	function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeFieldsFromTab("Root.Main", array(
			"Url","DisplayPriority","Cluster", "Enabled", "Visible", "Queryable","ogc_name","ogc_map", "ogc_transparent"
		));

		$geometryType = $fields->fieldByName("Root.Main.GeometryType");
		$LayerCategory = $fields->fieldByName("Root.Main.LayerType");
		$fields->removeFieldFromTab("Root.Main","GeometryType");
		$fields->removeFieldFromTab("Root.Main","LayerType");

		$ogc_format = $fields->fieldByName("Root.Main.ogc_format");
		$fields->removeFieldFromTab("Root.Main","ogc_format");


		$LayerType = $fields->fieldByName("Root.Main.Type");
		$fields->removeFieldFromTab("Root.Main","Type");

		$fields->addFieldsToTab("Root.Main", 
			array(
				new LiteralField("DisLabel","<h2>Layer Settings</h2>"),
				// Display parameters
				new CompositeField( 
					new CompositeField( 
						new LiteralField("OGCLabel","<h3>Display Settings</h3>"),
						new CheckboxField("Enabled", "Enabled <i>(To disable this layer from the frontend side, please set the checkbox status.)</i>"),
						new NumericField("DisplayPriority", "Draw Priority"),
						$geometryType,
						$LayerCategory,
						new LiteralField("OGCLabel","<i>Use the layer type field to define the layer behaviour (overlay: selectable, background: static data, contextual: base map).</i>"),
						new FieldGroup(
							new CheckboxField("Visible","Visible"),
							new CheckboxField("Queryable", "Queryable"),
							new CheckboxField("Cluster", "Cluster")						
						),
						new LiteralField("MapLabel","<i>\"Cluster\" can be applied to all WFS layers of all geometry types, but will transform non-point layers to points.</i>")
					),
					new CompositeField( 
						new LiteralField("URLLabel","<h3>OGC Server Settings</h3>"),
						new TextField("Url", "URL"),
						new TextField("ogc_map", "Map filename"),
						new LiteralField("MapLabel","<i>Optional: Path to UMN Mapserver Mapfile</i>"),
						$LayerType,
						new TextField("ogc_name", "Layer Name"),
						new LiteralField("MapLabel","<i>(as defined in GetCapabilities)</i>")
					),
					new CompositeField(
						new LiteralField("WMSLabel","<br /><h3>OGC WMS parameters</h3>"),
						new LiteralField("WMSDescription","The following parameters are required for OGC-WMS layers only."),
						$ogc_format,
						new CheckboxField("ogc_transparent", "Transparency")
					)
					
				)
			)
		);
		return $fields;
	}
	
	/**
	 * Creates and returns a layer definition array which will be used to configure
	 * open layers on the JavaScript side.
	 *
	 * @return array
	 */
	function getConfigurationArray() {
		$config = array();
		
		$layerType = $this->getField('Type');
		
		$config['Type']        = $this->getField('Type');
		$config['Title']       = $this->getField('Title');
		$config['Url']         = $this->getField('Url');
		$config['Visible']     = $this->getField('Visible');
		$config['ogc_name']    = $this->getField('ogc_name');
		
		$config['GeometryType']= $this->getField('GeometryType');
		$config['Cluster']    = $this->getField('Cluster');
		
		// create options element
		$options = array();
		$options['map']  = $this->getField("ogc_map");
				
		// handle layer type: WMS (tiled and untiled)
		if ($layerType == 'wms' || $layerType == 'wmsUntiled') {
			$options['SSID']        = $this->getField('ID');
			$options['layers']      = $this->getField("ogc_name");
			$options['transparent'] = $this->getField("ogc_transparent") ? "true": "false";
			$options['format']      = $this->getField('ogc_format');
		} else 
		// handle layer type: WFS
		if ($layerType == 'wfs') {
			$options['SSID']     = $this->getField('ID');		
			$options['typename'] = $this->getField("ogc_name");	
		}

		$config['Options'] = $options;

		return $config;
	}	
		

	/**
	 * Wrapper class to handle OGC get-feature requests for all kind of 
	 * layer types.
	 *
	 * @throws OLLayer_Exception
	 *
	 * @param integer featureID
	 * @throws OLLayer_Exception
	 *
	 * @return string XML response
	 */
	function getFeatureInfo($featureID) {
		$Type = $this->getField('Type');
		$url  = $this->getField('Url');
		
		$response = null;
		if ($Type == 'wms' || $Type == 'wmsUntiled') {
			$requestString = $this->getWMSFeatureRequest($param);
		} else 
		if ($Type == 'wfs') {
			
			$param = array();
			$param['featureID'] = $featureID;
			$requestString = $this->getWFSFeatureRequest($param);
		} else {
			// layer type unknown -> error
			throw new OLLayer_Exception('Request type unknown');
		}
		
		// send request to OGC web service
		$request  = new RestfulService($url,0);
		$response = $request->request($requestString);

		$xml = $response->getBody();
		return $xml;
	}


	/**
	 * Returns the GET request string for a OGC WFS get-feature request.
	 *
	 * @param array array of request parameters (featureID)
	 *
	 * @return string request string 
	 */
	public function getWFSFeatureRequest($param) {
		// http://202.36.29.39/cgi-bin/mapserv?map=/srv/www/htdocs/mapdata/spittelr/stations.map&request=getfeature&service=wfs&version=1.0.0&typename=Beam_trawl&OUTPUTFORMAT=gml3&featureid=Beam_trawl.6
		// http://202.36.29.39/cgi-bin/mapserv?map=/srv/www/htdocs/mapdata/spittelr/stations.map&request=getfeature&service=wfs&version=1.0.0&typename=stationdetails&OUTPUTFORMAT=gml3&featureid=stationdetails.106
		$featureID = $param['featureID'];
		$featureID = Convert::raw2xml($featureID);
		
		$ogcFeatureId = $this->getField('ogc_name').".".$featureID;
		$map          = $this->getField('ogc_map');
		$typename     = $this->getField('ogc_name');
		
		if ($typename == '') {
			throw new OLLayer_Exception('Invalid featuretype name. This layer has not been initialized correctly.');
		}
		
		$requestString = "?";
		if ($map) {
			$requestString = "?map=".$map."&";
		}
		
		$requestString .= "request=getfeature&service=WFS&version=1.0.0&typename=".$typename."&OUTPUTFORMAT=gml3&featureid=".$ogcFeatureId;
		return $requestString;
	}

	/**
	 *
	 * Parameter structure:
	 * 		$param['BBOX']
	 *		$param['WIDTH']
	 * 		$param['HEIGHT']
	 * 		$param['x']
	 * 		$param['z']
	 * @param array $param array object, storing the WMS relevant parameter information.
	 */
	public function getWMSFeatureRequest($param) {
		
		if (!isset($param['BBOX']) ) {
			throw new OLLayer_Exception('Parameter missing: BBOX');
		}

		if (!isset($param['x']) ) {
			throw new OLLayer_Exception('Parameter missing: x');
		}

		if (!isset($param['y']) ) {
			throw new OLLayer_Exception('Parameter missing: y');
		}

		if (!isset($param['WIDTH']) ) {
			throw new OLLayer_Exception('Parameter missing: WIDTH');
		}

		if (!isset($param['HEIGHT']) ) {
			throw new OLLayer_Exception('Parameter missing: HEIGHT');
		}
		
		if ($this->ogc_name == '') {
			throw new OLLayer_Exception('Feature type of the layer is not defined.');
		}
		
		
		$staticParams = array(
			'REQUEST' => 'GetFeatureInfo', 
			'INFO_FORMAT' => 'application/vnd.ogc.gml', 
			'VERSION' => '1.1.1', 
			'TRANSPARENT' => 'true', 
			'STYLE' => '', 
			'EXCEPTIONS' => 'application/vnd.ogc.se_xml', 
			'FORMAT' => 'image/png',
			'SRS' => 'EPSG%3A4326'
		);
		//$vars = $data->getVars();
		$URLRequest = "?";
		if ($this->ogc_map != '') {
			$URLRequest = "?map=".$this->ogc_map."&";
		}
		
		foreach($staticParams as $k => $v){
			$URLRequest .= $k.'='.$v.'&';
		}
		$URLRequest .= "LAYERS=".$this->ogc_name."&QUERY_LAYERS=".$this->ogc_name."&BBOX=".$param['BBOX'];
		$URLRequest .= "&x=".$param['x']."&y=".$param['y']."&WIDTH=".$param['WIDTH']."&HEIGHT=".$param['HEIGHT'];
		$URLRequest = trim($URLRequest,"&");
		return $URLRequest;
	}

}

/**
 * Customised exception class
 */
class OLLayer_Exception extends Exception {
}