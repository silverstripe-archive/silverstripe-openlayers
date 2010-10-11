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
	
	
	static $openlayers_path = "openlayers/javascript/jsparty/lib/OpenLayers.js";
	
	static function set_openlayers_path($value) {
		self::$openlayers_path = $value;
	}

	static function get_openlayers_path() {
		return self::$openlayers_path;
	}
	
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
		
		$openlayerJS = self::get_openlayers_path();
		return $openlayerJS;
	}
}
