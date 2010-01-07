<?php
/**
 * @package openlayers
 * @subpackage tests
 */

/**
 * This unit tests uses the ReflexionProxy class to perform a request and evaluate
 * the response.
 */
class ReflectionProxyTest extends FunctionalTest implements TestOnly {

	static $use_draft_site = true;
		
	/**
	 * Test the index method of the ReflectionProxy.
	 */
	function testAccessReflectionProxy() {		
		$response = $this->get('ReflectionProxy_Controller');
		$this->assertEquals($response->getBody(), "failed");
	}

	/**
	 * Test the reflextion method which should return the request as a json object.
	 * Used for unit testing only.
	 */
	function testReflection() {		
		$response = $this->get('ReflectionProxy_Controller/doprocess?param=param1');

		$obj = json_decode($response->getBody(),1);

		$this->assertEquals($obj['param'], "param1");
		$this->assertEquals($obj['isget'], true);
	}

	/**
	 * Test the reflextion method which should deny access.
	 */
	function testReflection_PermissionDeny() {	
		ReflectionProxy_Controller::$allowedIP = array('noname');	
		$response = $this->get('ReflectionProxy_Controller/doprocess?param=param1');

		$obj = json_decode($response->getBody(),1);

		$this->assertEquals($response->getBody(), "failed");
	}


}
