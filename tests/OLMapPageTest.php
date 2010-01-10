<?php

class OLMapPageTest extends FunctionalTest {
	
	static $fixture_file = 'openlayers/tests/OLMapPageTest.yml';
	static $use_draft_site = true;
	
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
		
		$response = $this->get('openlayers-map-1');
		$expectedFragment = '<div id="map"></div>';
		$this->assertContains($expectedFragment, $this->content());
		
		
		echo (Director::isDev());
		echo "DONE";A
		$expectedFragment = '/openlayers/javascript/jsparty/lib/OpenLayers.js';
		$this->assertContains($expectedFragment, $this->content());
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

		$this->assertEquals( $result['Map']['MinScale'], '200');
		$this->assertEquals( $result['Map']['MaxScale'], '400');
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

}