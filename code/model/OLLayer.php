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
	
	/** 
	 * Example for DTIS:
	 * Cluster Popup Header:
	 * - There are $stations.count DTIS stations
	 * Cluster Attributes:
	 * - Station $Station ($Cruise) <% if Depth %>: $Depth m depths<% end_if %>
	 */
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
		
		//
		"XMLWhitelist"		=> "Varchar(255)",
		"Labels"			=> "Varchar(255)",
		"SinglePopupHeader"	=> "Varchar(255)",
		
		// 
		"ClusterPopupHeader"=> "Varchar(255)",
		"ClusterAttributes" => "Varchar(255)",
		
		
		// temporarily values (shall be re-factored and removed later)
		"ogc_name"			=> "Varchar(100)",		// layer name (ogc layer name/id)
		"ogc_map"			=> "Varchar(1024)",		// url to the map file on the server side
		"ogc_format"		=> "Enum(array('png','jpeg','png24','gif','image/png','image/jpeg','image/gif'),'png')",
		"ogc_transparent"	=> "Boolean"			// transparent overlay layer
	);
	
	static $has_one = array(
		'Map' => 'OLMapObject',
		'StyleMap' => 'OLStyleMap'
	);	
	
	static $field_labels = array(
		"Type"             => "OGC API",
		"ogc_name"         => "OGC Layer Name",
		"ogc_map"          => "Map-filename",
		"ogc_format"       => "Image Format",
		"ogc_transparent"  => "Transparency",
		"Map.Title"        => "Map Name",
		"XMLWhitelist"     => "Get Feature XML Whitelist (comma separated)",
		"SinglePopupHeader" => "Single Feature Popup Header (i.e. <strong><em>Site</em></strong> DTI.22)",
		"ClusterPopupHeader" => "Features Cluster Popup Header (i.e. 'There are \$items.TotalItems Sites'></strong>)"
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

	static $searchable_fields = array('Title','ogc_name','LayerType','Type','Enabled','Visible','Cluster');

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
	

	/**
	 * Getter method for single-info-bubble template name.
	 */
	public function get_map_popup_detail_template($templateName = '') {
		return 'MapPopup_Detail';
	}

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
     * @codeCoverageIgnore
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

		$clusterPopupHeader = $fields->fieldByName("Root.Main.ClusterPopupHeader");
		$clusterAttributes = $fields->fieldByName("Root.Main.ClusterAttributes");
		
		$fields->removeFieldFromTab("Root.Main","ClusterPopupHeader");
		$fields->removeFieldFromTab("Root.Main","ClusterAttributes");

		$XMLWhitelist = $fields->fieldByName("Root.Main.XMLWhitelist");
		$Labels = $fields->fieldByName("Root.Main.Labels");
		$SinglePopupHeader = $fields->fieldByName("Root.Main.SinglePopupHeader");

		$fields->removeFieldFromTab("Root.Main","XMLWhitelist");
		$fields->removeFieldFromTab("Root.Main","Labels");
		$fields->removeFieldFromTab("Root.Main","SinglePopupHeader");

		//
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
	
		$fields->addFieldsToTab("Root.MapPopup", 
			array(
				new LiteralField("label01","<h3>Map Information Popup - Single Item</h3>"),
				new TextField("SinglePopupHeader", "Popup Header"),
				new LiteralField("SinglePopupHeader_description","<strong>Popup Header</strong>: Static text line for popup header for this layer, i.e., '<strong><em>Selected Item:</em></strong>)'. If no value is provided, the layer title will be shown instead.<br/><br/>"),
				
				new TextField("XMLWhitelist", "Attributes"),
				new LiteralField("XMLWhitelist_description","<strong>Attributes</strong>: comma separated list of attributes (available via the OGC interface).<br/><br/>"),
				
				new TextField("Labels", "Labels for Attributes"),
				new LiteralField("Labels_description","<strong>Attributes</strong>: comma separated list of lables for the attributes (see Attributes).<br/><br/>"),

				new LiteralField("label02","<h3>Map Information Popup - Multiple Items</h3>"),
				new TextField("ClusterPopupHeader", "Popup Header"),
				new LiteralField("ClusterPopupHeader_description","<strong>Attributes</strong>: Text line for cluster popup header, i.e., 'There are \$items.TotalItems Sites'></strong>).<br/><br/>"),
				
				new TextField("ClusterAttributes", "Attributes"),
				new LiteralField("ClusterAttributes_description","<strong>Attributes</strong>: comma separated list of lables for the attributes (see Attributes).<br/><br/>")	
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
		
		$config['StyleMapName'] = '';
		if ($this->StyleMap()) {
			$config['StyleMapName']    = $this->StyleMap()->getField('Name');
		}
		
		
		// create options element
		$options = array();
		$options['url_params']['map']  = $this->getField("ogc_map");
				
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
	* Find for layer WhiteList words in the XML response... 
	*
	* @param string $XMLTag XML source 
	* @param string $Keywords WhiteList words (comma separated) | null
	**/
	function WhiteList($XMlTag , $keywords = null){
		if ($keywords == null) {
			$keywords = $this->XMLWhitelist;
		}
		
		$patterns = explode(",",$keywords);
	    foreach($patterns as $pattern){
			$pattern = trim($pattern);
			if($pattern != ""){
	        	if (strpos($XMlTag,$pattern)) {
					return true;
	       		}
			}
	    }
	    return false;
	}
	
	/**
	* Find for labels for the whiteListed items... 
	*
	* @param string $XMLTag XML source 
	* @param string $Keywords WhiteList words (comma separated) | null
	**/
	function WhiteListLabels($keyword = null){
		$result = $keyword;
		$keywords = $this->XMLWhitelist;
		$labels = $this->Labels;
		
		$keywordlist = explode(",",$keywords);
		$labellist = explode(",",$labels);
		
		if ($labels != '') {
			$index = 0;
			// Number of Catches,Min-Depth, Max-Depth
		    foreach($keywordlist as $key) {
				if ($index > count($labellist) ) {
					break;
				}
				
				if ($key == $keyword) {
					$result = $labellist[$index];
					break;
				}
				$index++;
			}
		}
		if ($result == '') {
			$result = $keyword;
		}
		return $result;
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
		
		// concat feature-type name if not provided in featureID string.
		$ogcFeatureId = $featureID;
		if (strpos($featureID,".") === FALSE) {
			$ogcFeatureId = $this->getField('ogc_name').".".$featureID;
		}
		
		$map          = $this->getField('ogc_map');
		$typename     = $this->getField('ogc_name');
		$extraParams = (isset($param['ExtraParams'])) ? $param['ExtraParams'] : '';
				
		if ($typename == '') {
			throw new OLLayer_Exception('Invalid featuretype name. This layer has not been initialized correctly.');
		}
		
		$requestString = "?";
		if ($map) {
			$requestString = "?map=".$map."&";
		}
		
		// should this be configured from the cms?
		$requestString .= "request=getfeature&service=WFS&version=1.0.0&typename=".$typename."&OUTPUTFORMAT=gml3&featureid=".$ogcFeatureId.$extraParams;
		
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
	
	/**
	 * Gets the request result, converts it from XML to DOS and returns it.
	 * gets Whitelist words from the layer and finds tags into the XML file.
	 *
	 * @param Int $featureID Station (feature) ID
	 * @param String $extraParams, extra param for the request (normally coming from JS)
	 * @return DataObjectSet $obj, set of results 
	**/
	function doSingleStationRequest($featureID, $extraParams = '', $XMLWhitelist){
		
		if(!$featureID) {
			throw new OLLayer_Exception('Wrong params');
		}
		
		$atts = array();
		
		$params = array('featureID' => $featureID, 'ExtraParams' => $extraParams);
		
		$output = $this->getFeatureInfo($params);
		$obj = new DataObjectSet();
		
		$reader = new XMLReader();
		$reader->XML($output);
		
		$attributes = explode(",",$XMLWhitelist);
		
		// loop xml for attributes 
		while ($reader->read()) {
			if($reader->nodeType != XMLReader::END_ELEMENT && $reader->readInnerXML() != ""){
				if($this->WhiteList($reader->name)){
					$atts[$reader->name] = $reader->readInnerXML();
				}
			}
		}
		$reader->close();

		if($attributes) foreach($attributes as $attribute){
			if(array_key_exists("ms:".$attribute,$atts)){
				$obj->push(new ArrayData(array(
					'attributeName' => $this->WhiteListLabels($attribute),
					'attributeValue' => $atts["ms:".$attribute]
				)));
			}
		}
		return $obj;
	}


	/**
	 * Gets the request result, converts it from XML to DOS and returns it.
	 * gets Whitelist words from the layer and finds tags into the XML file.
	 *
	 * @param Int $featureID Station (feature) ID
	 * @param String $extraParams, extra param for the request (normally coming from JS)
	 *
	 * @return DataObjectSet $obj, set of results 
	**/
	function getFeature($featureID, $extraParams = ''){
		$namespace = 'ms';

		if(!$featureID) {
			throw new OLLayer_Exception('Wrong params');
		}
		
		$currentFeatureID = '';

		$params = array('featureID' => $featureID, 'ExtraParams' => $extraParams);
		$output = $this->getFeatureInfo($params);

		$obj = new DataObjectSet();

		$reader = new XMLReader();
		$reader->XML($output);
		
		// loop xml for attributes 
		$attributes = array();
		while ($reader->read()) {
			if($reader->nodeType != XMLReader::END_ELEMENT && $reader->readInnerXML() != ""){

				// skip ms:msFeatureCollection
				if ($reader->name != $namespace.":msFeatureCollection") {

					// strip ms: namespace from xml response
					if (substr_compare($reader->name,$namespace.":",0,3) === 0) {
						$name = substr_replace($reader->name,'',0,3);

						if ($name != $this->ogc_name) {
							$attributes[$name] = $reader->readInnerXML();
						} else {

							// found a new ID tag which is at the beginning of
							// an new feature item.
							if (!empty($attributes)) {
								$attributes['FeatureID'] = $currentFeatureID;
								$obj->push(new ArrayData($attributes));
							}
							$currentFeatureID = $reader->getAttribute('gml:id');
							$attributes = array();
						}
					}
				}
			}
		}
		
		if (!empty($attributes)) {
			$attributes['FeatureID'] = $currentFeatureID;
			$obj->push(new ArrayData($attributes));
		}
		$reader->close();
		return $obj;
	}
	
	/**
	* Function to render popup for one station (attributes).
	*
	* @param Int $featureID Station (feature) ID
	* @param String $stationID Name of the station (layers plus number)
	* @param String $extraParams extra params from JS
	* @param Int $mapID, the mapObjectID, it is needed sometimes to find specific layers that belong to the map
	*
	* @return String HTML - rendered information bubble
	**/
	public function renderBubbleForOneFeature($featureID, $stationID, $extraParams = '', $mapID = null, $templateName=''){
		$out = new ViewableData();

		$obj = $this->doSingleStationRequest($featureID, $extraParams, $this->XMLWhitelist);
		$out->customise( 
			array( 
				"attributes" => $obj, 
				"StationID" => $stationID, 
				'MapID' => $mapID, 
				'PopupHeader' => $this->SinglePopupHeader, 
				'LayerName' => $this->Title
			) 
		);

		return $out->renderWith($this->get_map_popup_detail_template($templateName));
	}
	
	/**
	* Function to render popup for cluster items.
	*
	* @param DataObjectSet $obj A list items, shown on the template, created by {@see OLMapPage->dogetfeatureinfo}.
	* @param String $extraParam, extra param, normally from JS.
	* @return String HTML - rendered information bubble
	**/
	public function renderClusterInformationBubble( $stationIDList, $extraParam = null, $templateName='') {		
		
		// multiple stations, render list
		$obj = new DataObjectSet();
		
		$listItemTemplate = 'Station: $FeatureID';
		if ($this->ClusterAttributes) {
			$listItemTemplate = $this->ClusterAttributes;
		}
		$obj = $this->getFeature($stationIDList , $extraParam);
		
		foreach($obj as $item) {
			$item->classname = "content_".str_replace('.','_',$item->FeatureID);
		}

		$template = '<% control items %>';
		$template .= sprintf('<li><a onClick="multipleStationSelect(\'$FeatureID\');return false;">%s</a></li><div class=\'$classname\'></div>',$listItemTemplate);
		$template .= '<%  end_control %>';
		
		$data = new ArrayData(array(
			"items" => $obj,
			"count" => $obj->Count(), 
		));	
		
		$header = 'There are $count Items.';
		
		if ($this->ClusterPopupHeader) {
			$header = $this->ClusterPopupHeader;
		}
		
		$viewer = SSViewer::fromString($header);
			
		$clusterPopupHeader = $viewer->process($data);
		
		$viewer = SSViewer::fromString($template);
		$stationListTemplate = $viewer->process($data);
				
		$out = new ViewableData();
		$out->customise( array( 
			"stationList" =>  $stationListTemplate,
			"PopupHeader" => $clusterPopupHeader
		));
		
		return $out->renderWith('MapPopup_List');
	}
}

/**
 * Customised exception class
 */
class OLLayer_Exception extends Exception {}