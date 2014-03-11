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
	private static $singular_name = 'Layer';
	
	private static $plural_name = 'Layers';	
	
	public static $wfs_pagesize = 8;
	
	private static $db = array (
		"Title"				=> "Varchar(50)",
		"Url" 				=> "Varchar(1024)",
		"LayerType"		  	=> "Enum(array('overlay','background','contextual'),'overlay')",
		"Type"			  	=> "Enum(array('wms','wfs','wmsUntiled','Google Streets','Google Physical','Google Hybrid','Google Satellite'),'wfs')",
		"DisplayPriority" 	=> "Int",
		"Enabled"         	=> "Boolean",
		"Visible"         	=> "Boolean",
		"Queryable"			=> "Boolean",
		"Baselayer"			=> "Boolean",

		"GeometryType"		=> "Enum(array('Point','Polygon','Line','Raster'),'Point')",
		"Cluster"			=> "Boolean",
		"Opacity"			=> "Float",

        "ReducedLayer" => "Boolean",
        "full_ogc_name" => "Varchar(100)",

        //
        "UseTemplateForPopupWindow" => "Boolean",

		//
		"XMLWhitelist"		=> "Varchar(255)",
		"Labels"			=> "Varchar(255)",
		"SinglePopupHeader"	=> "Varchar(255)",

		//
		"ClusterPopupHeader"=> "Varchar(255)",
		"ClusterAttributes" => "Varchar(255)",

		"Popup_SingleInformation"  => "Text",
        "Popup_ClusterInformation"  => "Text",


		// temporarily values (shall be re-factored and removed later)
		"ogc_name"			=> "Varchar(100)",		// layer name (ogc layer name/id)
		"ogc_map"			=> "Varchar(1024)",		// url to the map file on the server side
		"ogc_format"		=> "Enum(array('png','jpeg','png24','gif','image/png','image/jpeg','image/gif'),'png')",
		"ogc_transparent"	=> "Boolean"			// transparent overlay layer
	);

	private static $has_one = array(
		'Map' => 'OLMapObject',
		'StyleMap' => 'OLStyleMap'
	);	
	
	private static $field_labels = array(
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
	
	private static $summary_fields = array(
		'Title',
		'ogc_name',
		'Type',
		'GeometryType',
		'DisplayPriority',
		'Enabled',
		'Visible',
		'Cluster',
		'Queryable',
		'ogc_transparent',
		'Map.Title'
	 );

	private static $searchable_fields = array('Title','ogc_name','LayerType','Type','Enabled','Visible','Cluster','Map.Title');

	private static $defaults = array(
	    'DisplayPriority' => 50,
	    'Opacity' => 1,
	    'Enabled' => true,
	    'Visible' => false,
	    'Queryable' => true,
	    'ogc_transparent' => true,
		'GeometryType' => 'Point',
		'LayerType' => 'overlay',
		'XMLWhitelist' => ''
	 );

	private static $casting = array(
		'Enabled' => 'Boolean',
	);

	private static $default_sort = "Title ASC";
	
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
	 * @return fieldset
	 */
	function getCMSFields() {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js');
		Requirements::javascript('openlayers/javascript/ollayeradmin.js');

		$fields = parent::getCMSFields();

		$fields->removeFieldsFromTab("Root.Main", array(
			"Url","DisplayPriority","Cluster", "Enabled", "Visible", "Queryable","ogc_name","ogc_map", "ogc_transparent"
		));

		$geometryType = $fields->fieldByName("Root.Main.GeometryType");
		$opacity = $fields->fieldByName("Root.Main.Opacity");
		$LayerCategory = $fields->fieldByName("Root.Main.LayerType");

        $fields->removeFieldFromTab("Root.Main","GeometryType");
		$fields->removeFieldFromTab("Root.Main","Opacity");
		$fields->removeFieldFromTab("Root.Main","LayerType");

		$ogc_format = $fields->fieldByName("Root.Main.ogc_format");
		$fields->removeFieldFromTab("Root.Main","ogc_format");

		$LayerType = $fields->fieldByName("Root.Main.Type");
		$fields->removeFieldFromTab("Root.Main","Type");

        $reducedLayerFieldObject = $fields->fieldByName("Root.Main.ReducedLayer");
        $fields->removeFieldFromTab("Root.Main","ReducedLayer");

        $fullOGCNameFieldObject = $fields->fieldByName("Root.Main.full_ogc_name");
        $fields->removeFieldFromTab("Root.Main","full_ogc_name");

        $clusterPopupHeader = $fields->fieldByName("Root.Main.ClusterPopupHeader");
		$clusterAttributes = $fields->fieldByName("Root.Main.ClusterAttributes");

		$baselayer = $fields->fieldByName("Root.Main.Baselayer");
		$fields->removeFieldFromTab("Root.Main","Baselayer");

		$styleMaps = $fields->fieldByName("Root.Main.StyleMapID");
		$fields->removeFieldFromTab("Root.Main","StyleMapID");

		$fields->removeFieldFromTab("Root.Main","ClusterPopupHeader");
		$fields->removeFieldFromTab("Root.Main","ClusterAttributes");

		$XMLWhitelist = $fields->fieldByName("Root.Main.XMLWhitelist");
		$Labels = $fields->fieldByName("Root.Main.Labels");
		$SinglePopupHeader = $fields->fieldByName("Root.Main.SinglePopupHeader");

		$fields->removeFieldFromTab("Root.Main","XMLWhitelist");
		$fields->removeFieldFromTab("Root.Main","Labels");
		$fields->removeFieldFromTab("Root.Main","SinglePopupHeader");

        $wmsCompositeField = new CustomCompositeField (
            new LiteralField("WMSLabel","<br /><h3>OGC WMS Configuration</h3>"),
            $baselayer,
            $opacityField = new OpacityNumericField('Opacity', 'Opacity', $this->Opacity),
            $ogc_format,
            $OGCTransparencyField = new CheckboxField("ogc_transparent", "Transparency")
        );

        $wmsCompositeField->addClassName('wmscomposite');
        $wmsCompositeField->addClassName('wmsUntiledcomposite');
        $wmsCompositeField->addClassName('ogccomposite');


        $baselayer->setDescription("Each map must have one base layer.");
        $opacityField->setDescription("Define layer's opacity, use number between 0.0 (fully transparent) to 1.0 (not transparent).<br>50% opacity can be entered as 0.5.");
        $ogc_format->setDescription("Defines the MIME/Type for getmap requests.");
        $OGCTransparencyField->setDescription("Use this flag if the layer shall have alpha channel, if available.");

        $wfsCompositeField = new CustomCompositeField (
            new LiteralField("WFSLabel","<br /><h3>OGC WFS Configuration</h3>"),
            $styleMaps,
            $reducedLayerFieldObject,
            $FullLayerName = new TextField("full_ogc_name", "Detailed Layer Name"),
            $ClusterField = new CheckboxField("Cluster", "Cluster")
        );
        $wfsCompositeField->addClassName('wfscomposite');
        $wfsCompositeField->addClassName('ogccomposite');

        $styleMaps->setDescription("Define the render style of the vector layer. Use Style Maps admin to define those styles.<br/>If no style is selected, the layer will be rendered with a default style.");
        $reducedLayerFieldObject->setDescription("Enable this if the vector layer is very large and the layer rendered is only a subset of the available <br/>attributes. The Detailed Layer Name field defines the OGC WFS layer which will be used to<br/>retrieve all attribute data, i.e. when the user clicks on a feature.");
        $FullLayerName->setDescription("Define a second WFS layer which will be used to retrieve attribute data from a seelcted feature.");
        $ClusterField->setDescription("Cluster can be applied ideally for point layers and group large datasets to cluster points.");

		//
		$fields->addFieldsToTab("Root.Main",
			array(
                // Display parameters
                new CompositeField(
                    new LiteralField("OGCLabel","<h2>Display Settings</h2>"),
                    $EnabledField = new CheckboxField("Enabled", "Enabled"),
                    $VisibleField = new CheckboxField("Visible","Visible"),
                    $geometryType,
                    $LayerCategory,
                    $QueryableField = new CheckboxField("Queryable", "Queryable"),
                    $DisplayPriorityField = new NumericField("DisplayPriority", "Draw Priority")
                ),
                new CompositeField(
                    new LiteralField("OGCSettings","<h2>OGC Server Settings</h2>"),
                    new TextField("Url", "URL"),
                    $mapParameterField = new TextField("ogc_map", "Map filename"),
                    $LayerType,
                    $LayerNameField = new TextField("ogc_name", "Layer Name")
                ),
                $wmsCompositeField,
                $wfsCompositeField
            )
		);

        $mapParameterField->setDescription("Optional: Path to UMN Mapserver mapfile.");
        $LayerType->setDescription('Define the source of data and its behaviour on the map. The URL will be appended as a <br/>vendor parameter to each OGC request.').

        $EnabledField->setDescription("Flag if the layer is shown and used on the map.");
        $DisplayPriorityField->setDescription("Higher numbered layers will be drawn on top or lower numbered layers.");
        $LayerCategory->setDescription("Use layer type to define its behaviour: <br/>Overlay: selectable, background: static data, contextual: base map.");
        $VisibleField->setDescription("Sets the default state of the map if it is visible or hidden.");
        $QueryableField->setDescription("Sets the behaviour is the user can query items on the map for this layer.");
        $LayerNameField->setDescription("Use the OGC layer name as it appears in the GetCapabilities XML document.");

		$fields->addFieldsToTab("Root.MapPopup",
			array(
                new CheckboxField("UseTemplateForPopupWindow", "Use SilverStripe HTML templates"),

                new LiteralField("divPopupAttributes1","<div class='divAttributesTemplate'>"),

                new LiteralField("label01","<h3>Popup Window - Single Item</h3>"),
				new TextField("SinglePopupHeader", "Popup Header"),
				new LiteralField("SinglePopupHeader_description","<strong>Popup Header</strong>: Static text line for popup header for this layer, i.e., '<strong><em>Selected Item:</em></strong>)'. If no value is provided, the layer title will be shown instead.<br/><br/>"),

				new TextField("XMLWhitelist", "Attributes"),
				new LiteralField("XMLWhitelist_description","<strong>Attributes</strong>: comma separated list of attributes (available via the OGC interface).<br/><br/>"),

				new TextField("Labels", "Labels for Attributes"),
				new LiteralField("Labels_description","<strong>Attributes</strong>: comma separated list of lables for the attributes (see Attributes).<br/><br/>"),

				new LiteralField("label02","<h3>Popup Window - Multiple Items</h3>"),
				new TextField("ClusterPopupHeader", "Popup Header"),
				new LiteralField("ClusterPopupHeader_description","<strong>Attributes</strong>: Text line for cluster popup header, i.e., 'There are \$items.TotalItems Sites'></strong>).<br/><br/>"),

				new TextField("ClusterAttributes", "Attributes"),
				new LiteralField("ClusterAttributes_description","<strong>Attributes</strong>: comma separated list of lables for the attributes (see Attributes).<br/><br/>"),

                new LiteralField("divPopupAttributes2","</div>"),

                new LiteralField("divPopupTemplate1","<div class='divPopupTemplate'>"),
                new LiteralField("label04","<h3>Popup Window</h3>"),
                new TextareaField('Popup_SingleInformation','Template - Single Item',$this->Popup_SingleInformation),
                new TextareaField('Popup_ClusterInformation','Template - Cluster Items',$this->Popup_ClusterInformation),
                new LiteralField("divPopupTemplate2","</div>"),

                new LiteralField("label03","<h3>Available Attributes (WFS layers only)</h3>"),
                new LiteralField('describeFeatureType',"<a href='#' class='describeFeatureType' data-id='".$this->ID."' onclick='return false;'>List  Labels</a>"),
                new LiteralField('featureTypeAttributes',"<div id='featureTypeAttributes'><ul><li>Not loaded...</li></ul></div>"),
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
	 * @see OLMapObject::getConfigurationArray()
	 *
	 * @todo use a template to generate the javascript instead of using a array. Already implemented in the OpenSource module.
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
		$config['isBaseLayer'] = $this->getField('Baselayer')?true:false;

		$config['GeometryType']= $this->getField('GeometryType');
		$config['Cluster']     = $this->getField('Cluster');
		$config['opacity']     = $this->getField('Opacity');

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
	 * @see OLLayer::doSingleStationRequest(), OLLayer::getFeature(), Atlas_Controller::loadStation()
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
        die("BANG");
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
	function WhiteListLabels($keyword = null) {
        $keywords = explode(',',$this->XMLWhitelist);
        $labels   = explode(',',$this->Labels);
        $keywords = array_combine($keywords,$labels);
        return $keywords[$keyword];
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

        if ($this->getField('ReducedLayer') == true) {
            $typename     = $this->getField('full_ogc_name');

            $ogcFeatureId = str_replace($this->getField('ogc_name'),$this->getField('full_ogc_name'),$ogcFeatureId);
        }

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

	function getFeatureLabels() {
		$request = $this->Url + 'request=DescribeFeatureType&service=WFS&version=1.0.0&TYPENAME='+$this->ogc_name;
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

        $obj = new ArrayList();
        $atts = array();

		if(!$featureID) {
			throw new OLLayer_Exception('Wrong params');
		}


		$params = array('featureID' => $featureID, 'ExtraParams' => $extraParams);

        $responseXML = $this->getFeatureInfo($params);

//        echo"<pre>";
//        print_r($responseXML);
//        echo"</pre>";

        $doc  = new DOMDocument();
        $doc->loadXML($responseXML);

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace("ms", "http://mapserver.gis.umn.edu/mapserver");
        $xpath->registerNamespace("gml", "http://www.opengis.net/gml");
        $xpath->registerNamespace("ogc", "http://www.opengis.net/ogc");
        $xpath->registerNamespace("xsi", "http://www.w3.org/2001/XMLSchema-instance");

        $featureList = $xpath->query('gml:featureMember');
        foreach($featureList as $featureType) {
            if ($this->ReducedLayer == true) {
                $feature = $xpath->query('ms:'.$this->full_ogc_name, $featureType);
            } else {
                $feature = $xpath->query('ms:'.$this->ogc_name, $featureType);
            }
            $featureItem = $feature->item(0);

            $feautureID = $featureItem->getAttribute('gml:id');
            $atts['FeatureID'] = $feautureID;

            $attributes = $xpath->query('ms:*',$featureItem);
            foreach ($attributes as $attribute) {
                if ($attribute->nodeName != 'ms:msGeometry') {
                    $atts[str_replace('ms:','',$attribute->nodeName)] = $attribute->nodeValue;
                }
            }
        }
        return new ArrayData($atts);
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

		$obj = new ArrayList();

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

		$attributes = $this->doSingleStationRequest($featureID, $extraParams, $this->XMLWhitelist);

        $data = new ArrayData(array(
				"attributes" => $attributes,
				"StationID" => $stationID,
				'MapID' => $mapID,
				'PopupHeader' => $this->SinglePopupHeader,
				'LayerName' => $this->Title
			)
		);

        if ($this->UseTemplateForPopupWindow) {
            $template = $this->Popup_SingleInformation;
            $viewer = SSViewer::fromString($template);
            return $viewer->process($data);
        }

        // 2do: deprecated method
        $filter = explode(',',$this->XMLWhitelist);
        $filter = array_combine($filter,$filter);

        $attributes = array_intersect_key($attributes->toMap(), $filter);
        $obj = new ArrayList();
        foreach($attributes as $key => $attribute) {
            $obj->push(new ArrayData(array(
                'attributeName' => $this->WhiteListLabels($key),
                'attributeValue' => $attribute
            )));
        }

        $out = new ViewableData();
        $out->customise(array(
            "attributes" => $obj,
            "StationID" => $stationID,
            'MapID' => $mapID,
            'PopupHeader' => $this->SinglePopupHeader,
            'LayerName' => $this->Title
        ) );

        return $out->renderWith($this->get_map_popup_detail_template($templateName));
	}

    public function renderClusterInformationBubble($stationIDList, $extraParam = null, $templateName='') {

        // multiple stations, render list
        $obj = new ArrayList();
        $obj = $this->getFeature($stationIDList , $extraParam);
        foreach($obj as $item) {
            $item->classname = "content_".str_replace('.','_',$item->FeatureID);
        }

        $data = new ArrayData(array(
            "items" => $obj,
            "count" => $obj->Count(),
        ));

        if ($this->UseTemplateForPopupWindow) {
            $template = $this->Popup_ClusterInformation;

            $viewer = SSViewer::fromString($template);
            return $viewer->process($data);
        }

        // @to2: deprecate this

        $listItemTemplate = 'Station: $FeatureID';
        if ($this->ClusterAttributes) {
            $listItemTemplate = $this->ClusterAttributes;
        }

        // 1st partial template: create template for content area
		$template = '<% loop items %>';
		$template .= sprintf('<li><a onClick="multipleStationSelect(\'$FeatureID\');return false;">%s</a></li><div class=\'$classname\'></div>',$listItemTemplate);
		$template .= '<%  end_loop %>';

        // 2nd partial template: create template for header area

		$header = 'There are $count Items.';
		if ($this->ClusterPopupHeader) {
			$header = $this->ClusterPopupHeader;
		}

        // render partial tempates
		$viewer = SSViewer::fromString($header);
		$clusterPopupHeader = $viewer->process($data);

		$viewer = SSViewer::fromString($template);
		$stationListTemplate = $viewer->process($data);

        // render combined tempate
		$out = new ViewableData();
		$out->customise( array(
			"stationList" =>  $stationListTemplate,
			"PopupHeader" => $clusterPopupHeader
		));
		return $out->renderWith('MapPopup_List');
	}

	/**
	 * Returns an array (non associated) with all attributes of the OGC feature type.
	 * It requests the labels from the OGC WFS server via the DescribeFeatureType
	 * call and parses the XML document.
	 *
	 * Geometry attribute is filtered and not returned.
	 *
	 * @return Array List of attributes
	 */
	function describeFeatureType() {
		$requestString = "?request=DescribeFeatureType&service=WFS&version=1.0.0&TYPENAME=%s";
		$requestString = sprintf($requestString,Convert::raw2xml($this->ogc_name));

		//
		// // send request to OGC web service
		$request  = new RestfulService($this->Url,0);
		$response = $request->request($requestString);
		if ($response->getStatusCode() != 200) {
			throw new Exception('Remote Server did not respond.');
		}

		$xml = $response->getBody();

		$strMatch = "<?xml version='1.0' encoding=\"ISO-8859-1\" ?>";
		$pos =  strpos($xml,$strMatch);

		if ($pos === false) {
            throw new Exception('Remote Server did not respond.');
        }

		$reader = new XMLReader();
		$reader->XML($xml);

		$attributes = array();
		$attributesReached = false;

		while ($reader->read()) {
            if($reader->nodeType != XMLReader::END_ELEMENT) {
                if ($reader->name == 'sequence') {
                    $attributesReached = true;
                }
                if ($attributesReached && $reader->name == 'element') {


                    $name = $reader->getAttribute('name');
                    $type = $reader->getAttribute('type');

                    if ($type != 'gml:GeometryPropertyType') {
                        $attributes[] = $name;
                    }
                }
            }
        }
		$reader->close();

		unset($request);
		unset($reader);

		return $attributes;
	}
}

/**
 * Customised exception class
 */
class OLLayer_Exception extends Exception {}