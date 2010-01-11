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
	
	/**
	 * varaible to store the open layers instance in the controller class.
	 * @var OpenLayers openLayers
	 */
	protected $openLayers = null;

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
		
		Requirements::javascript('openlayers/javascript/jquery/jquery-1.3.2.min.js');
		Requirements::javascript('openlayers/javascript/jquery/jquery-ui-1.7.2.custom.min.js');
		//Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		
		Requirements::javascript('openlayers/javascript/OLMapWrapper.js');
		Requirements::javascript('openlayers/javascript/OLStyleFactory.js');
		Requirements::javascript('openlayers/javascript/OLMapPage.js');
		Requirements::javascript('themes/niwa/javascript/jquery.checkbox.js');
		Requirements::javascript('themes/niwa/javascript/SSPopup.js');
		Requirements::javascript('themes/niwa/javascript/SSPanZoomBar.js');
		Requirements::javascript('themes/niwa/javascript/jquery.jcarousel.pack.js');
		
		
		Requirements::themedCSS('OLMapPage');

		// serialize map cofiguration 
		$config = $page->getDefaultMapConfiguration();		
		
		// add url segment for this page (required for js ajax calls).
		$config['PageName'] = $this->getField('URLSegment');
		
		// create json string
		$jsConfig = "var ss_config = ".json_encode($config);
		
		// add configuration json object to custom scripts
		Requirements::customScript($jsConfig);
	}
	
	
	/**
	* Function to render popup for one station (attributes)
	* @param Object $layer The layer the station belongs to.
	* @param Int $featureID Station (feature) ID
	* @param String $stationID Name of the station (layers plus number)
	**/
	static function renderSingleStation($layer, $featureID, $stationID){
		$atts = array();
		$output = $layer->getFeatureInfo($featureID);
		
		$obj = new DataObjectSet();
		$out = new ViewableData();
		$reader = new XMLReader();
		$reader->XML($output);
		
		// loop xml for attributes 
		while ($reader->read()) {
			if($reader->nodeType != XMLReader::END_ELEMENT){
				if(self::WhiteList($reader->name,$layer->XMLWhitelist)){
					$atts[$reader->name] = $reader->readInnerXML();
				}
			}
		}
		$reader->close();
		foreach($atts as $key => $value){
			$obj->push(new ArrayData(array(
				'attributeName' => $key,
				'attributeValue' => $value
			)));
		}
		$out->customise( array( "attributes" => $obj, "StationID" => $stationID ) );
		return $out->renderWith('MapPopup_Detail');
	}
	
	/**
	* Find for layer WhiteList words in the XML response...
	*
	* @param string $haystack XML source 
	* @param string $needles WhiteList words (comma separated)
	**/
	static function WhiteList($XMlTag , $keywords){
		$patterns = explode(",",$keywords);
	    foreach($patterns as $pattern){
			$pattern = trim($pattern);
	        if (strpos($XMlTag,$pattern)) {
				
				return true;
	       }
	    }
	    return false;
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
	 * Returns the HTML response for the map popup-box. After the user clicks
	 * on the map, the CMS will send of a request to the OGC server to request
	 * a XML data structure for the features on that selected location, parses
	 * the XML response and renders the HTML, which will be returned to the
	 * popup window.
	 *
	 * @param Request $data
	 *
	 * @return string HTML segment
	 */
	public function dogetfeatureinfo( $request ) {
		
		$output = "Sorry we cannot retrieve feature information, please try again";
		// check if the request is for more than one station (clustered)
		$stationID = Director::urlParam("OtherID");
		// condition for single station, create request and render template
		if(strpos($stationID,",") === FALSE){
			// process request parameters
			$mapid   = (int)Director::urlParam("ID"); 
			$feature = explode(".",Director::urlParam("OtherID")); 
			
			// test if user requests feature-info for one element (otherwise create 
			// overview template.
		
			$layerName = Convert::raw2sql($feature[0]);
			$featureID = Convert::raw2sql($feature[1]);
		
			$layer = DataObject::get_one('OLLayer',"ogc_name = '{$layerName}' AND MapID = '{$mapid}'");

			if($layer){
				
				return self::renderSingleStation($layer, $featureID, $stationID);
				
			}
		// multiple stations, render list
		} else{
			$stationIDs = explode(",",$stationID);
			$obj = new DataObjectSet();
			$out = new ViewableData();
			foreach($stationIDs as $stationID){
				$obj->push(new ArrayData(array(
					'Station' => $stationID
				)));
			}
			$out->customise( array( "stations" => $obj ) );
			return $out->renderWith('MapPopup_List');
		}

		return $output;
	}
	
	
	
}
