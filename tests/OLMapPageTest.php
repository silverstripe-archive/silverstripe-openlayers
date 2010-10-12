<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage tests
 */

class OLMapPageTest extends FunctionalTest {
	
	static $fixture_file = 'openlayers/tests/OLMapPageTest.yml';
	static $use_draft_site = true;
	
	static $atlas_controller = 'ReflectionProxy_Controller/doXML';
	
	/**
	 * Test getCMSFields  (basic test)
	 */
	function testGetCMSFields() {
		
		$obj = new OLMapPage();
		$fieldset = $obj->getCMSFields();		

		$this->assertTrue(is_a($fieldset, "FieldSet"));
	}
		
	/**
	 * Test page rendering (basic test)
	 */
	function testPageRendering() {

		$ol_path = "openlayers/javascript/jsparty/openlayers-2.10/OpenLayers.js";
		OpenLayersModel::set_openlayers_path($ol_path);
		
		$response = $this->get('openlayers-map-1');
		$expectedFragment = '<div id="map"></div>';
		$this->assertContains($expectedFragment, $this->content());
		
		$this->assertContains($ol_path, $this->content());
	}

	/**
	 * Test getDefaultMapConfiguration
	 */
	function testDefaultMapConfiguration() {
		$map = $this->objFromFixture('OLMapObject', 'map');

		// page page without a map
		$obj    = $this->objFromFixture('OLMapPage', 'mappage_1');
		$result = $obj->getDefaultMapConfiguration();
		$this->assertEquals(sizeof($result),0);
		

		// page page with a map
		$obj = $this->objFromFixture('OLMapPage', 'mappage_2');		
		$result = $obj->getDefaultMapConfiguration();

		$this->assertEquals(sizeof($result),3);
		
		$this->assertEquals( $result['Map']['Title'], 'map title');
	}
	
	/**
	 * Test getLayerlistForTemplate
	 */
	function testLayerlistForTemplate() {

		// page page with a map
		$obj = $this->objFromFixture('OLMapPage', 'mappage_2');		

		$map = $obj->getComponent('Map');
		
		$layer = new OLLayer();
		$layer->Title = 'Layer 1';
		$layer->Enabled = True;
		$layer->LayerType = 'overlay';
		$map->Layers()->Add($layer);

		$layer = new OLLayer();
		$layer->Title = 'Layer 2';
		$layer->Enabled = True;
		$layer->LayerType = 'background';
		$map->Layers()->Add($layer);
		
		$result = $obj->getLayerlistForTemplate();	
		
		$idList = $result->getField('overlayLayers')->getIdList();
		$this->assertEquals(array("1"=>"1"),$idList);

		$idList = $result->getField('backgroundLayers')->getIdList();
		$this->assertEquals(array("2"=>"2"),$idList);
	}
	
	/**
	* OLMapPage_Controller
	**/
	
	// Test renderSingleStation with valid params
	function testrenderSingleStation_validParams(){
		
		$url = Director::absoluteBaseURL() . self::$atlas_controller;
		$featureID = 2;
		$expectedFragment = "<h4 class=\"popup\">Station stationdetails.$featureID</h4>";
		
		$layer = new OLLayer();
		$layer->ID = 1;
		$layer->Title = 'allStations';
		$layer->Url = $url;
		$layer->ogc_map = 'testmap';
		$layer->ogc_name = 'stationdetails';
		$layer->Type = "wfs";
		$layer->MapID = 1;
		$layer->write();
		
		$mapPage = new OLMapPage_Controller();
		$resp = $layer->renderBubbleForOneFeature('1',"stationdetails.$featureID");
		$this->assertTrue(is_string($resp));
		$this->assertContains($expectedFragment, $resp);
		

	}
	
	// Test renderSingleStation with invalid params
	function testrenderSingleStation_invalidParams(){
		
		$url = Director::absoluteBaseURL() . self::$atlas_controller;
		$featureID = 2;
		$expectedFragment = "<h4 class=\"popup\">Station stationdetails.$featureID</h4>";
		
		$layer = new OLLayer();
		$layer->ID = 1;
		$layer->Title = 'allStations';
		$layer->Url = $url;
		$layer->ogc_map = 'testmap';
		$layer->ogc_name = 'stationdetails';
		$layer->Type = "wfs";
		$layer->MapID = 1;
		$layer->write();
		
		$mapPage = new OLMapPage_Controller();
		
		//first param is not an object or is a wrong object (OLLayer)
		try {
			$resp = $layer->renderBubbleForOneFeature($featureID,"stationdetails.$featureID");
			
		}
		catch(Exception $e) {
			$this->assertEquals("Wrong Layer class", $e->getMessage());
			return;
		}
		
		//first param is not an object or is a wrong object (OLLayer)
		try {
			$resp = $layer->renderBubbleForOneFeature(null,"stationdetails.$featureID");
			
		}
		catch(Exception $e) {
			$this->assertEquals("Wrong params", $e->getMessage());
			return;
		}	

	}
	
	function testdogetfeatureinfo(){
		
		$url = Director::absoluteBaseURL() . self::$atlas_controller;
		
		$mapPage = new OLMapPage();
		$mapPage->URLSegment = 'themap';
		$mapurl = $mapPage->URLSegment;
		$mapPage->MapID = 1;
		$mapPage->write();
		$layer = new OLLayer();
		$layer->ID = 1;
		$layer->Title = 'allStations';
		$layer->Url = $url;
		$layer->ogc_map = 'testmap';
		$layer->ogc_name = 'stationdetails';
		$layer->MapID = 1;
		$layer->write();
		$Map = new OLMapObject();
		$Map->ID = 1;
		$Map->Title= "Map Title";
		$Map->MinScale="200";
		$Map->AtlasLayerID = 1;
		$Map->write();
		
		$resp = $this->get(Director::absoluteURL($mapurl."/dogetfeatureinfo/1/stationdetails.1"));
		$this->assertContains("Station stationdetails.1", $resp->getBody());
		
		// wrong params
		try {
			$resp = $this->get(Director::absoluteURL($mapurl."/dogetfeatureinfo/1/stationdetails"));
			
		}
		catch(Exception $e) {
			$this->assertEquals("Wrong params", $e->getMessage());
			return;
		}
		
		// empty params
		try {
			$resp = $this->get(Director::absoluteURL($mapurl."/dogetfeatureinfo/1/"));
			
		}
		catch(Exception $e) {
			$this->assertEquals("Empty params", $e->getMessage());
			return;
		}
	}
	
	//test dogetfeatureinfo
	
	
	
	/*
	//test FormLayerSwitcher
	function testFormLayerSwitcher(){
		
		$mapPage = new OLMapPage_Controller();
		$resp = $mapPage->FormLayerSwitcher();
		var_dump($resp);
	}
	*/
}