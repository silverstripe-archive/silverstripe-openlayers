<?php
/**
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
		
	function testDoRequestNullValues() {		
		$response = $this->get('Proxy/dorequest');
		$this->assertEquals($response->getBody(), "Invalid request.");
	}

	function testDoRequest_AccessDeny() {		
		
		// set proxy configuration
		Proxy_Controller::set_allowed_host( array('some_domainname'));

		// generate request
		$url = Director::absoluteBaseURL() . 'ProxyTest_Controller';
		$u = 'u='.$url.'?param=proxyTest';		
		
		$response = $this->get('Proxy/dorequest?'.$u);

		// verify/assert response
		$this->assertEquals($response->getBody(), "Access denied to (http://localhost/niwa_os2020/ProxyTest_Controller?param=proxyTest).");
	}

	function testDoRequest_AccessDeny_MultipleDomains() {		
		
		// set proxy configuration
		Proxy_Controller::set_allowed_host( array('some_domainname','some_other_domainname'));

		// generate request
		$url = Director::absoluteBaseURL() . 'ProxyTest_Controller';
		$u = 'u='.$url.'?param=proxyTest';		
		
		$response = $this->get('Proxy/dorequest?'.$u);

		// verify/assert response
		$this->assertEquals($response->getBody(), "Access denied to (http://localhost/niwa_os2020/ProxyTest_Controller?param=proxyTest).");
	}


	function testDoRequest_ValidRequest() {		
		// set proxy configuration
		Proxy_Controller::set_allowed_host( array('localhost'));
		
		// generate request
		$url = Director::absoluteBaseURL() . 'ReflectionProxy_Controller';
		$u = 'u='.$url.'/getrecords?param=proxyTest&no_header=1';		
		
		$response = $this->get('Proxy/dorequest?'.$u);

		// convert response into array
		$obj = json_decode($response->getBody(),1);
		
		// verify/assert response
		$this->assertEquals($obj['url'], "/niwa_os2020/ReflectionProxy_Controller/getrecords");
		$this->assertEquals($obj['param'], "proxyTest");
		$this->assertEquals($obj['isget'], true);
	}

	function testDoRequest_ValidRequest_MultipleDomain() {		
		// set proxy configuration
		Proxy_Controller::set_allowed_host( array('localhost','some_domainname'));
		
		// generate request
		$url = Director::absoluteBaseURL() . 'ReflectionProxy_Controller';
		$u = 'u='.$url.'/getrecords?param=proxyTest&no_header=1';		
		
		$response = $this->get('Proxy/dorequest?'.$u);

		// convert response into array
		$obj = json_decode($response->getBody(),1);
		
		// verify/assert response
		$this->assertEquals($obj['url'], "/niwa_os2020/ReflectionProxy_Controller/getrecords");
		$this->assertEquals($obj['param'], "proxyTest");
		$this->assertEquals($obj['isget'], true);
	}

}


