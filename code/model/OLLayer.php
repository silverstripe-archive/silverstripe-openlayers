<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage model
 */

/** 
 * Sapphire CMS: Open Layer 
 *
 * Each instance of an open layer class is represented as a dataobject in 
 * Sapphire
 * This class is used to configure OpenLayers and what information should be shown
 * on the map.
 */
class OLLayer extends DataObject {
	
	public static $singular_name = 'Layer';
	
	public static $plural_name = 'Layers';	
	
	public static $wfs_pagesize = 8;
	
	static $db = array (
		"Title"				=> "Varchar(50)",
		"Url" 				=> "Varchar(1024)",
		"LayerType"		  	=> "Enum(array('overlay','background','contextual'),'overlay')",
		"Type"			  	=> "Enum(array('wms','wfs','wmsUntiled','Google Streets','Google Physical','Google Hybrid','Google Satellite'),'wfs')",
		"DisplayPriority" 	=> "Int",		
		"Enabled"         	=> "Boolean",
		"Visible"         	=> "Boolean",
		"Queryable"			=> "Boolean",
		
		"GeometryType"		=> "Enum(array('Point','Polygon','Line','Raster'),'Point')",
		"Cluster"			=> "Boolean",
		"XMLWhitelist"		=> "Varchar(255)",
		
		// temporarily values (shall be re-factored and removed later)
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
		'XMLWhitelist' => 'attribute_1, attribute_2, attribute_3, attribute_4, attribute_5'
	 );

	static $casting = array(
		'Enabled' => 'Boolean',
	);
	
	static $default_sort = "Title ASC";
	
	static function get_wfs_pagesize() {
		return self::$wfs_pagesize;
	}

	static function set_wfs_pagesize($value) {
		return self::$wfs_pagesize = $value;
	}


	/**
	 * Overwrites SiteTree.getCMSFields.
	 *
	 * This method creates a customised CMS form for back-end user.
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
	 * Get the configuration array for OpenLayers
	 *
	 * Creates and returns a layer definition array. This array will be used 
	 * to configure OpenLayers on the JavaScript side. This method is called
	 * by {@link OLMapObject::getConfigurationArray()}.
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
	 * This method sends OGC get-feature requests for this layer to the OGC webservice.
	 *
	 * This method generates OGC getFeature / getFeatureInfo requests (depending on its 
	 * configuration (WFS/WMS), sends if via a restful service to the OGC service
	 * and return the response body.
	 * This method does not perform a response validation.
	 *
	 * The parameter for this method needs to be of this structure:
	 * For WFS layers:
	 *
	 * $params = array ('featureID' => string)	: OGC ID for the selected feature.
	 *
	 * For WMS layers:
 	 *
	 * $params = array (
	 *		'BBOX' => 'minx,miny,maxx,maxy' : comma separated list of bbox coordinates
	 *		'x': integer	: x position of the mouse click (in pixels)
	 *		'y': integer	: y position of the mouse click (in pixels)
	 *		'WIDTH': integer	: width of the map-image (in pixels)
	 *		'HEIGHT': integer : height of the map-image (in pixels)
	 * )
	 *
	 * @throws OLLayer_Exception
	 *
	 * @param array $params array of parameters
	 *
	 * @return string XML response
	 */
	function getFeatureInfo($params) {
		$Type = $this->getField('Type');
		$url  = $this->getField('Url');
		$response = null;
		
		if (!is_array($params)) {
			throw new OLLayer_Exception('Invalid request parameter.');
		}
		
		if ($Type == 'wms' || $Type == 'wmsUntiled') {
 			$param = array();

			if (!isset($params['BBOX'])) {
				throw new OLLayer_Exception('Mandatory parameter is missing: BBOX.');
			}

			if (!isset($params['x'])) {
				throw new OLLayer_Exception('Mandatory parameter is missing: x.');
			}

			if (!isset($params['y'])) {
				throw new OLLayer_Exception('Mandatory parameter is missing: y.');
			}

			if (!isset($params['WIDTH'])) {
				throw new OLLayer_Exception('Mandatory parameter is missing: WIDTH.');
			}

			if (!isset($params['HEIGHT'])) {
				throw new OLLayer_Exception('Mandatory parameter is missing: HEIGHT.');
			}
			$requestString = $this->getWMSFeatureRequest($params);
		} else 
		if ($Type == 'wfs') {
			if (!isset($params['featureID'])) {
				throw new OLLayer_Exception('Mandatory parameter is missing: featureID.');
			}			
			$requestString = $this->getWFSFeatureRequest($params);
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
	 * Returns the OGC 'getfeature' request string for a OGC WFS get-feature request.
	 * Add pagination kind of approach. When the pageNum parameter has been set, use
	 * this parameter to define the number of max-features we require to render 
	 * the requested page type. Otherwise, request all features.
	 *
	 * @param array $param array of request parameters (see {@link getFeatureInfo})
	 *
	 * @return string request string 
	 */
	public function getWFSFeatureRequest($param) {
	
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
		
		// should this be configured from the cms?
		$requestString .= "request=getfeature&service=WFS&version=1.0.0&typename=".$typename."&OUTPUTFORMAT=gml3&featureid=".$ogcFeatureId;
		
		// Apply pagingation if required
		if (isset($param['pageNum'])) {
			$page = Convert::raw2sql($param['pageNum']);
			$pagesize = self::get_wfs_pagesize();

			$requestString .= sprintf("&maxfeatures=%s", (($pagesize*($page))+1) );
		}
		return $requestString;
	}

	/**
	 * Returns the OGC 'getfeature' request string for a OGC WMS getFeatureInfo request.
	 *
	 * @param array $param array of request parameters (see {@link getFeatureInfo})
	 *
	 * @return string request string 
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
class OLLayer_Exception extends Exception {}