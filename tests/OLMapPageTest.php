<?php

class OLMapPageTest extends FunctionalTest {
	
	static $fixture_file = 'openlayers/tests/OLMapPageText.yml';
	static $use_draft_site = true;
	
	function testRunMigration() {
		
		// publish the page
		// $mapPage = $this->objFromFixture('OLMapPage', 'olmappage');
		// $mapPage->publish("Stage", "Live");	
		
		// $response = Director::test('openlayers-map');
		$response = $this->get('openlayers-map');
		// <div id="map"></div>
		var_dump($response);
	}
}