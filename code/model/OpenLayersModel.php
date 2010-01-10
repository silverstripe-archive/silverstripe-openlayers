<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage code
 */

/**
 *
 */
class OpenLayersModel {

	
	function __construct() {
	}
	
	/**
	 * Returns the relative URL to the OpenLayers JavaScript library.
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

class OpenLayersModel_Exception extends Exception {}