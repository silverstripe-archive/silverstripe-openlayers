<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage code
 */

/**
 * Core MapPage class. The map page will render the OpenLayer map and create
 * the required html document structure to ensure that the map viewer operates
 * according to the current setup (defined in @see OLLayer and @OLMapObject).
 */
class OLMapPage extends Page {
	
	static $db = array(
	);	
	
	static $has_one = array(
		'Map' => 'OLMapObject'		// default map shown when the page opens
	);

	/**
	 * Overwrites SiteTree.getCMSFields.
	 *
	 *
     * @codeCoverageIgnore
	 *
	 * @return fieldset
	 */
	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$maps  = DataObject::get("OLMapObject");
		$items = array();

		// get all assigned agent-members
		if ($maps) {
			// get first member (agents)
			$items = $maps->map('ID','Title');
		}

		$fields->addFieldsToTab("Root.Content.OpenLayers", 
			array(
				new LiteralField("MapLabel","<h2>Map Selection</h2>"),
				// Display parameters
				new CompositeField( 
					new CompositeField( 
						new LiteralField("DefLabel","<h3>Default OpenLayers Map</h3>"),
						new DropdownField("MapID", "Map", $items, $this->MapID, null, true)
					)
				)
			)
		);
		return $fields;
	}
	
	/**
	 * This method returns the default configuration array structure of the 
	 * default map. It is used to initialize the OpenLayer JavaScript classes
	 * after the page has been loaded.
	 *
	 * @return array Configuration array which is processed by JS:initMap
	 */
	public function getDefaultMapConfiguration() {
		$result    = array();
		$mapObject = $this->GetComponent('Map');
		if($mapObject && $mapObject->ID != 0) {
			
			$result = $mapObject->getConfigurationArray();

			$cont = Controller::curr();
			$request = $cont->getRequest();
			
			if ($request) {
				if ($request->getVar('bbox')) {
				
					$param = $request->getVar('bbox');
					$array = preg_split("/[\s]*[,][\s]*/", $param);
				
					if (sizeof($array) == 4) {
						$result['Latitude'] = '';
						$result['Longitude'] = '';
						$result['Zoom'] = '';
				
						$extent['left'] = $array[0];
						$extent['top'] = $array[1];
						$extent['right'] = $array[2];
						$extent['bottom'] = $array[3];
						$result['Map']['DefaultExtent'] = $extent;
					}
				}
			}
		}
		return $result;
	}

	public function getStyleMap() {
		$result = null;
		$mapObject = $this->GetComponent('Map');
		if($mapObject && $mapObject->ID != 0) {
			$result = $mapObject->getJSStyleMaps();
		}
		return $result;
	}

	/**
	 * Returnslayers in a customised viewable data object 
	 * to render the layer control of the default map. 
	 *
	 * @return ViewableData
	 */
	public function getLayerlistForTemplate( ) {
		$mapObject = $this->GetComponent('Map');
		$obj = new ViewableData();
		
		$result = array();
		if($mapObject) {
			$overlayLayers    = $mapObject->getComponents('Layers',"Enabled = 1 AND LayerType = 'overlay'",'DisplayPriority DESC');
			$backgroundLayers = $mapObject->getComponents('Layers',"Enabled = 1 AND LayerType = 'background'",'DisplayPriority DESC');
			
			$obj->setField("MapPage", $this);
			$obj->setField("overlayLayers", $overlayLayers);
			$obj->setField("backgroundLayers", $backgroundLayers);
		}
		return $obj;
	}
}

/**
 * Controller Class for Main OpenLayers Page
 *
 * Page controller class for OpenLayersPage (@link OpenLayersPage). The controller
 * class handles the requests and delegates the requests to the page instance
 * as well as to the available OGC webservices.
 */
class OLMapPage_Controller extends Page_Controller {
	
	static $allowed_actions = array(
		'dogetfeatureinfo'
	);
	
	public static $url_handlers = array(
		'dogetfeatureinfo/$ID/$OtherID/$ExtraID' => 'dogetfeatureinfo'
	);
	
	/**
	 * varaible to store the open layers instance in the controller class.
	 * @var OpenLayers openLayers
	 */
	protected $openLayers = null;
	
	static function GoogleMapAPIKey() {
		global $googlemap_api_keys;
		$environment = Director::get_environment_type();

		$api_key = null;
		if (isset($googlemap_api_keys["$environment"])) {
			$api_key = $googlemap_api_keys["$environment"];
		}
		return $api_key;
	}

	/**
	 * Returns the open layers instance (via singleton pattern).
	 *
	 * @return OpenLayers model class for the open layers implementation.
	 */
	function getOpenLayers() {
		if ($this->openLayers == null) {
			$this->openLayers = new OpenLayersModel();
		}
		return $this->openLayers;
	}

	/**
	 * Initialisation function that is run before any action on the controller is called.
	 */
	public function init() {
		
		parent::init();
		
		$page = $this->data();
		
		$openLayers = $this->getOpenLayers();
		Requirements::javascript( $openLayers->getRequiredJavaScript() );		

		Requirements::combine_files('jquery_min.js',array(	
			"openlayers/javascript/jquery/jquery-ui-1.7.2.custom.min.js"
		));
		
		Requirements::combine_files('openlayer_mappage.js',array(	
			"openlayers/javascript/OLMapWrapper.js",
			"openlayers/javascript/OLMapPage.js",
			"openlayers/javascript/jquery.checkbox.js",
			"openlayers/javascript/SSPopup.js",
			"openlayers/javascript/SSPanZoomBar.js"
		));
		
		Requirements::javascript('openlayers/javascript/OLStyleFactory.js');
		
		// we need to add call to js maps somehow, any better way?
		$googleCheck = DataObject::get_one('OLLayer',"(Type = 'Google Physical' OR Type = 'Google Hybrid' OR Type = 'Google Satellite' OR Type = 'Google Satellite') AND Enabled = 1");
		if($googleCheck){
			$api_key = self::GoogleMapAPIKey();
			Requirements::javascript("http://maps.google.com/maps?file=api&amp;v=2&amp;key={$api_key}&sensor=true");
		}
		
		
		Requirements::themedCSS('OLMapPage');

		// serialize map cofiguration 
		$config = $page->getDefaultMapConfiguration();		
		
		// add url segment for this page (required for js ajax calls).
		$config['PageName'] = $this->getField('URLSegment');
		
		// create json string
		$jsConfig = "var ss_config = ".json_encode($config);
		
		// add configuration json object to custom scripts
		Requirements::customScript($jsConfig);

		$jsStyleMap = $page->getStyleMap();		

		Requirements::customScript($jsStyleMap);
		
	}
	
	/**
	 * Render the layer selector.
	 *
	 * @return string HTML-string which represents the layer-list div object.
	 */
	function FormLayerSwitcher() {		
		$page = $this->data();
		
		$obj = $page->getLayerlistForTemplate();
		return $obj->renderWith('LayerControl');
	}

	/**
	 * Processes params and finds if request is for a single or multiple stations.
	 * if single station calls renderSingleStation method with station and layers values
	 * if multiple stations create list with stations to render HTML
	 * if not stationID displays message
	 *
	 * @param Request $data
	 *
	 * @throws OLLayer_Exception
	 *
	 * @return string HTML segment
	 */
	public function dogetfeatureinfo( $request ) {
		
		$parameters = $request->postVars();
		
		$templateName = '';
		
		if(isset($parameters['template'])){
			$templateName = $parameters['template'];
		}
		
		// 
		// CHECK IF PARAMETERS ARE VALID
		// 
		if(!isset($parameters['featureList'])){
			throw new OLLayer_Exception('Invalid parameter: mandatory parameters are missing.');
		}
		
		if($parameters['featureList'] == ""){
			throw new OLLayer_Exception('Invalid parameter: mandatory parameters are missing.');
		}
		
		$extraParam = '';
		// get the ExtraID (required for species list)
		if (isset($parameters['specieName'])) {
			$extraParam = $parameters['specieName'];
		}
		// check if the request is for more than one station (clustered)
		$stationID =  $parameters['featureList'];

		// create standard message.
		$output = "Sorry we cannot retrieve feature information, please try again.";
		
		// determin the layer via the provided feature ID
		$feature = explode(".", $stationID);
		 
		if(count($feature) <= 1) {
			throw new OLLayer_Exception('Invalid parameter: FeatureType name not present in current request.');
		}
		
		// we need the OLMapObject ID, so we can find layers that belong to this map object
		$mapid = $this->getComponent('Map')->ID; 

		$layerName = Convert::raw2sql($feature[0]);
		$featureID = Convert::raw2sql($feature[1]);
		
		$sqlWhere = "ogc_name = '{$layerName}' AND MapID = '{$mapid}' AND Enabled = 1";
		$layer = DataObject::get_one('OLLayer',$sqlWhere);
		if(!$layer) {
			throw new OLLayer_Exception('Invalid parameter: Unknown layer-name.');			
		}

		// 
		// RETRIEVE DATA FROM WFS SOURCE
		// 
		// condition for single station, create request and render template
		if (strpos($stationID,",") === FALSE) {		
			return $layer->renderBubbleForOneFeature( $featureID, $stationID, $extraParam, $mapid, $templateName);
		} else{
			return $layer->renderClusterInformationBubble( $stationID, $extraParam, $templateName);
		}
		return $output;
	}
}
