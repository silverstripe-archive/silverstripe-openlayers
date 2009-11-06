<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage code
 */

/**
 *
 */
class OpenLayersPage extends Page {
	
	static $db = array(
		'MapName' => 'Varchar(100)',
		'Latitude' => 'Decimal(12,8)',
		'Longitude' => 'Decimal(12,8)',
		'DefaultZoom' => 'Int'
	);	
	
	static $has_many = array(
		'Property' => 'OLMapProperty'
	);

	/**
	 * Overwrites SiteTree.getCMSFields to change the CMS form behaviour, 
	 *  i.e. by adding form fields for the additional attributes defined in 
	 * {@link OpenLayersPage::$db}.
	 */ 
	function getCMSFields() {
		$fields = parent::getCMSFields();

		// return the modified fieldset.
		return $fields;
	}

}

/**
 * Controller Class for Main OpenLayers Page
 *
 * Page controller class for OpenLayersPage (@link OpenLayersPage). The controller
 * class handles the requests and delegates the requests to the page instance
 * as well as to the available OGC webservices.
 */
class OpenLayersPage_Controller extends Page_Controller {
	
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
			$this->openLayers = new OpenLayers();
		}
		return $this->openLayers;
	}

	/**
	 * Initialisation function that is run before any action on the controller is called.
	 */
	public function init() {
		
		$openLayers = $this->getOpenLayers();

		parent::init();
		Requirements::javascript( $openLayers->getRequiredJavaScript() );		
		Requirements::javascript('openlayers/javascript/OpenLayersPage.js');
		Requirements::javascript('http://dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.1');		
	}
	
}
