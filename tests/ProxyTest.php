<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage tests
 */

/**
 * This unit test tests the proxy used for the mapping page. Currently, 
 * the proxy supports GET requests only.
 * This unit tests uses the ReflexionProxy class to perform a request and evaluate
 * the response.
 */
class ProxyTest extends FunctionalTest {

	static $use_draft_site = true;

	/**
	 * Test a proxy request without a proxy url
	 */
	function testDoRequestNullValues() {		
		$response = $this->get('Proxy/dorequest');
		$this->assertEquals($response->getBody(), "Invalid request: unknown proxy destination.");
	}

	/**
	 * Test a proxy request to a server. We want to test the permission deny case.
	 */
	function testDoRequest_AccessDeny() {		
		// set proxy configuration
		Proxy_Controller::set_allowed_host( array('some_domainname'));

		// generate request
		$url = Director::absoluteBaseURL() . 'ProxyTest_Controller';
		$url.= '?param=proxyTest';		
		
		$response = $this->get('Proxy/dorequest?u='.$url);

		// verify/assert response
		$this->assertEquals($response->getBody(), "Access denied to (".$url.").");
	}

	/**
	 * Test a proxy request to a server. We want to test the permission deny case.
	 */
	function testDoRequest_AccessDeny_MultipleDomains() {				
		// set proxy configuration
		Proxy_Controller::set_allowed_host( array('some_domainname','some_other_domainname'));

		// generate request
		$url = Director::absoluteBaseURL() . 'ProxyTest_Controller';
		$url.= '?param=proxyTest';		
		
		$response = $this->get('Proxy/dorequest?u='.$url);

		// verify/assert response
		$this->assertEquals($response->getBody(), "Access denied to (".$url.").");
	}

	/**
	 * Test a valid proxy request to a restful service. The proxy will grant
	 * access to that server.
	 */
	function testDoRequest_ValidRequest() {		
		// set proxy configuration
		Proxy_Controller::set_allowed_host( array('localhost','qa.silverstripe.com'));
		
		// generate request
		$url = Director::absoluteBaseURL() . 'ReflectionProxy_Controller';
		$u = 'u='.$url.'/doprocess?param=proxyTest&no_header=1';		
		
		$response = $this->get('Proxy/dorequest?'.$u);

		// convert response into array
		$obj = json_decode($response->getBody(),1);
		
		// get project-path from the absolute URL
		$baseUrl = explode('/',Director::absoluteBaseURL());
		$projectPath = $baseUrl[3];
		
		// verify/assert response
		$this->assertEquals($obj['url'], "/".$projectPath."/ReflectionProxy_Controller/doprocess");
		$this->assertEquals($obj['param'], "proxyTest");
		$this->assertEquals($obj['isget'], true);
	}

	/**
	 * Test a valid proxy request to a restful service. The proxy will grant
	 * access to that server, but allows is to access more then one server.
	 */
	function testDoRequest_ValidRequest_MultipleDomain() {		
		// set proxy configuration
		Proxy_Controller::set_allowed_host( array('localhost','qa.silverstripe.com','some_domainname'));
		
		// generate request
		$url = Director::absoluteBaseURL() . 'ReflectionProxy_Controller';
		$u = 'u='.$url.'/doprocess?param=proxyTest&no_header=1';		
		
		$response = $this->get('Proxy/dorequest?'.$u);

		// convert response into array
		$obj = json_decode($response->getBody(),1);
		
		// get project-path from the absolute URL
		$baseUrl = explode('/',Director::absoluteBaseURL());
		$projectPath = $baseUrl[3];
		
		// verify/assert response
		$this->assertEquals($obj['url'], "/".$projectPath."/ReflectionProxy_Controller/doprocess");
		$this->assertEquals($obj['param'], "proxyTest");
		$this->assertEquals($obj['isget'], true);
	}

	/**
	 * Test a valid proxy post request to a restful service. 
	 */
	function testDoRequest_PostRequest() {		

		// set proxy configuration
		Proxy_Controller::set_allowed_host( array('localhost','qa.silverstripe.com'));
		
		// generate request
		$url = Director::absoluteBaseURL() . 'ReflectionProxy_Controller';
		$u = $url.'/doprocess?param=proxyTest_Post';		
		
		$response = $this->post('Proxy/dorequest?',array('u'=>$u,'no_header'=>'1'));

		// convert response into array
		$obj = json_decode($response->getBody(),1);

		// get project-path from the absolute URL
		$baseUrl = explode('/',Director::absoluteBaseURL());
		$projectPath = $baseUrl[3];
		
		// verify/assert response
		$this->assertEquals($obj['url'], "/".$projectPath."/ReflectionProxy_Controller/doprocess");
		$this->assertEquals($obj['param'], "proxyTest_Post");
		$this->assertEquals($obj['isget'], false);
	}	
}
