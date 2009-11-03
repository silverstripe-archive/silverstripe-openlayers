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
	
	public static $db = array(
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
	 * Initialisation function that is run before any action on the controller is called.
	 */
	public function init() {
		
		parent::init();
		Requirements::javascript('openlayers/javascript/jsparty/OpenLayers.js');		
		Requirements::javascript('openlayers/javascript/OpenLayersPage.js');		
	}
	
}
