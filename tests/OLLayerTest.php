<?php

/**
 * @package openlayers
 * @subpackage tests
 */
class OLLayerTest extends SapphireTest {


	/**
	 * Initiate the controller and page classes and configure GeoNetwork service
	 * to use the mockup-controller for testing.
	 */
	function setUp() {
		parent::setUp();

	}

	/**
	 * Remove test controller from global controller-stack.
	 */
	function tearDown() {
		parent::tearDown();
	}


	/**
	 * Test response which an wfs layer object
	 */
	function testGetConfigurationArrayWFS() {
		
		$layer = new OLLayer();
		
		$layer->Title = "Title";
		$layer->Url = "Url";
		$layer->Type = "wfs";
		$layer->DisplayPriority = "1";

		$layer->Enabled = "1";
		$layer->Visible = "1";
		$layer->Queryable = "1";
		
		$layer->GeometryType = "Line";
		$layer->Cluster = "1";

		$layer->ogc_name = "ogc_name";
		$layer->ogc_map = "map";
		$layer->ogc_format = "jpeg";
		$layer->ogc_transparent = "1";
		$layer->write();

		$result = $layer->getConfigurationArray();

		$this->assertEquals($result['Type'], 'wfs');
		$this->assertEquals($result['Title'], 'Title');
		$this->assertEquals($result['Url'], 'Url');
		$this->assertEquals($result['Visible'], '1');
		$this->assertEquals($result['ogc_name'], 'ogc_name');
		$this->assertEquals($result['GeometryType'], 'Line');
		$this->assertEquals($result['Cluster'], '1');

		$options = $result['Options'];

		$this->assertEquals($options['map'], 'map');
		$this->assertEquals($options['SSID'], '1');
		$this->assertEquals($options['typename'], 'ogc_name');
	}

	/**
	 * Test response which an wfs layer object
	 */
	function testGetConfigurationArrayWMS() {
		
		$layer = new OLLayer();
		
		$layer->Title = "Title";
		$layer->Url = "Url";
		$layer->Type = "wms";
		$layer->DisplayPriority = "1";

		$layer->Enabled = "1";
		$layer->Visible = "1";
		$layer->Queryable = "1";
		
		$layer->GeometryType = "Line";
		$layer->Cluster = "1";

		$layer->ogc_name = "ogc_name";
		$layer->ogc_map = "map";
		$layer->ogc_format = "jpeg";
		$layer->ogc_transparent = "1";
		$layer->write();

		$result = $layer->getConfigurationArray();

		$this->assertEquals($result['Type'], 'wms');
		$this->assertEquals($result['Title'], 'Title');
		$this->assertEquals($result['Url'], 'Url');
		$this->assertEquals($result['Visible'], '1');
		$this->assertEquals($result['ogc_name'], 'ogc_name');
		$this->assertEquals($result['GeometryType'], 'Line');
		$this->assertEquals($result['Cluster'], '1');

		$options = $result['Options'];

		$this->assertEquals($options['map'], 'map');
		$this->assertEquals($options['SSID'], '2');
		$this->assertEquals($options['layers'], 'ogc_name');
		$this->assertEquals($options['transparent'], "true");
		$this->assertEquals($options['format'], 'jpeg');
	}
	
	/**
	 * Test response which an empty layer object
	 */
	function testGetConfigurationArrayEmpty() {
		
		$layer = new OLLayer();
		$layer->write();

		$result = $layer->getConfigurationArray();

		$this->assertEquals($result['Type'], NULL);
		$this->assertEquals($result['Title'], NULL);
		$this->assertEquals($result['Url'], NULL);
		$this->assertEquals($result['Visible'], false);
		$this->assertEquals($result['ogc_name'], NULL);
		$this->assertEquals($result['GeometryType'], 'Point');
		$this->assertEquals($result['Cluster'], NULL);

		$options = $result['Options'];

		$this->assertEquals($options['map'], NULL);
	}

	function testSendWMSFeatureRequest() {
	}
	
	function testSendWFSFeatureRequest() {
	}	
	
	function testGetFeatureInfo() {
	}
	
	
}
