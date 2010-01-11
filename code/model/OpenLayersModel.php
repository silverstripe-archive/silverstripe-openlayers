<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage model
 */

/**
 * Global OpenLayers Model class
 *
 * This class implements the global OpenLayers model class. At the moment,
 * the model class only stores the reference to the OpenLayers Javascript
 * files, but can grow over time. The {@link OLMapPage} stores a reference to 
 * this class.
 */
class OpenLayersModel {
	
	function __construct() {
	}
	
	/**
	 * Returns JS OpenLayers URL
	 *
	 * Depending on the current environment (development, production), this
	 * method returns the relative URL to the OpenLayers JavaScript library.
	 * For production/staging environment, the OpenLayer JavaScript library
	 * is compressed.
	 *
	 * @return string URL
	 */
	function getRequiredJavaScript() {
		$openlayerJS = "openlayers/javascript/jsparty/OpenLayers.js";
		
		if (Director::isDev() == true) {
			$openlayerJS = "openlayers/javascript/jsparty/lib/OpenLayers.js";
		}
		return $openlayerJS;
	}
}
