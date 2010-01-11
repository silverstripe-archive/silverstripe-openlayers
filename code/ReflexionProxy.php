<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage code
 */

/**
 * Mockup controller  to simulate the Proxy server side for unit-tests.
 * This controller returns the http request as a JSON string. The controller
 * is used for unit tests only and can be used on selected IP addresses 
 * (i.e. '::1','127.0.0.1').
 */
class ReflectionProxy_Controller extends Controller implements TestOnly {

	static $allowedIP = array('::1','127.0.0.1','192.168.1.16');
	
	/**
	 * Init method
	 *
	 * Disable the basic authentication for this controller.
	 */
	function init() {
		$this->disableBasicAuth();
//		BasicAuth::protect_entire_site(false);
		return parent::init();
	}

	/**
	 * Standard index method. Used for unit tests only.
	 */
	function index() {
		return "failed";
	}

	/**
	 * Processes requests and returns a JSON object.
	 *
	 * This method creates an array, storing all request parameters in that array
	 * and add request specific parameters. This array will be returned as a JSON object. 
	 * The calling unit test can validate the request sent to this controller {@link ProxyTest}.
	 *
	 * @return string json-encoded string for validation.
	 *
	 */	function doprocess($data) {
		if(!in_array($data->getIP(),self::$allowedIP)) {
			return "failed";
		}
		$params = $data->requestVars();
		$params['isget'] = $data->isGET();
		
		$response = json_encode($params);
		
		return $response;
	}

}
