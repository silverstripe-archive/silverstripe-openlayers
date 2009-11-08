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
	
	private $allowedHosts = null;
	
	
	function __construct() {
		$this->allowedHosts = array();
		$this->allowedHosts[] = '202.36.29.39';
		
	/*	
	    host = url.split("/")[2]
	    if allowedHosts and not host in allowedHosts:
	        print "Status: 502 Bad Gateway"
	        print "Content-Type: text/plain"
	        print
	        print "This proxy does not allow you to access that location (%s)." % (host,)
	        print
	        print os.environ
	*/		
	}
	/**
	 * Returns the relative URL to the OpenLayers JavaScript library.
	 *
	 * @return string URL
	 */
	function getRequiredJavaScript() {
		return "openlayers/javascript/jsparty/OpenLayers.js";
	}
	
	function getAllowedHosts() {
		
		var_dump($this->allowedHosts);

	}

}