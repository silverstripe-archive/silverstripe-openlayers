<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage tests
 */

class OpenLayersModelTest extends SapphireTest {
	
	
	/**
	 * Test getCMSFields  (basic test)
	 */
	function testgetRequiredJavaScript_InDev() {
		Director::set_environment_type('dev');
		
		$obj = new OpenLayersModel();
		$jscript = $obj->getRequiredJavaScript();		

		$this->assertEquals($jscript, "openlayers/javascript/jsparty/lib/OpenLayers.js");
	}

	/**
	 * Test getCMSFields  (basic test)
	 */
	function testgetRequiredJavaScript_InTest() {
		Director::set_environment_type('test');
		
		$obj = new OpenLayersModel();
		$jscript = $obj->getRequiredJavaScript();		

		$this->assertEquals($jscript, "openlayers/javascript/jsparty/OpenLayers.js");
	}

	/**
	 * Test getCMSFields  (basic test)
	 */
	function testgetRequiredJavaScript_InProd() {
		Director::set_environment_type('live');
		
		$obj = new OpenLayersModel();
		$jscript = $obj->getRequiredJavaScript();		

		$this->assertEquals($jscript, "openlayers/javascript/jsparty/OpenLayers.js");
	}

}