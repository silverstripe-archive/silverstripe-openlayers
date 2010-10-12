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

		$ol_path = "openlayers/javascript/jsparty/openlayers-2.10/OpenLayers.js";
		OpenLayersModel::set_openlayers_path($ol_path);
		
		$obj = new OpenLayersModel();
		$jscript = $obj->getRequiredJavaScript();		

		$this->assertEquals($jscript, $ol_path);

		$ol_path = "openlayers/javascript/jsparty/openlayers-2.10/lib/OpenLayers.js";
		OpenLayersModel::set_openlayers_path($ol_path);
		
		$obj = new OpenLayersModel();
		$jscript = $obj->getRequiredJavaScript();		

		$this->assertEquals($jscript, $ol_path);

	}

}