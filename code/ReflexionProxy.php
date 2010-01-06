<?php

/**
 * @package openlayers
 * @subpackage code
 *
 * Mockup controller class to simulate the Proxy server side for this test.
 */
class ReflectionProxy_Controller extends Controller {

	/**
	 * Standard method, not in use.
	 */
	function index() {
		BasicAuth::protect_entire_site(false);
		return "failed";
	}

	/**
	 * Returns the request parameters and request specific parameters so that 
	 * the calling unit test can perform the validation on the test {@see ProxyTest}.
	 *
	 * @return string json encoded string for validation
	 */
	function getrecords($data) {
		BasicAuth::protect_entire_site(false);
		
		if ($data->getIP() != '::1') {
			return 'failed';
		}
		$params = $data->requestVars();
		$params['isget'] = $data->isGET();
		
		$response = json_encode($params);
		return $response;

	}
}
