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
	 * Basic test to see if getCMSFields returns a fieldset.
	 */
	function testGetCMSFields() {
		
		$layer = new OLLayer();
		$fieldset = $layer->getCMSFields();		

		$this->assertTrue(is_a($fieldset, "FieldSet"));
	}
	
	/**
	 * Test WFS get-feature support (test with invalid layer)
	 */
	function testGetWFSFeatureRequest_invalidLayer() {
		$layer = new OLLayer();
		$param = array();
		$param['featureID'] = 1;
		
		try {
			$request = $layer->getWFSFeatureRequest( $param );		
		}
		catch(OLLayer_Exception $e) {
			return;
		}
		$this->assertTrue(false,"Exception expected but hasn't been thrown.");
	}

	/**
	 * Test WFS get-feature support (test with valid layer, no map-path)
	 */
	function testGetWFSFeatureRequest() {
		$layer = new OLLayer();
		$layer->ogc_name = "featureType";

		$param = array();
		$param['featureID'] = 2;
		
		$request = $layer->getWFSFeatureRequest( $param );		
		$this->assertEquals($request, "?request=getfeature&service=WFS&version=1.0.0&typename=featureType&OUTPUTFORMAT=gml3&featureid=featureType.2");
	}

	/**
	 * Test WFS get-feature support (test with valid layer, with map-path)
	 */
	function testGetWFSFeatureRequest_WithMapName() {
		$layer = new OLLayer();
		$layer->ogc_name = "featureType";
		$layer->ogc_map  = "TestMap";

		$param = array();
		$param['featureID'] = 2;
		
		$request = $layer->getWFSFeatureRequest( $param );		
		$this->assertEquals($request, "?map=TestMap&request=getfeature&service=WFS&version=1.0.0&typename=featureType&OUTPUTFORMAT=gml3&featureid=featureType.2");
	}

	function testGetWMSFeatureRequest_invalidLayer() {		
		$layer = new OLLayer();
		$layer->ogc_name = "featureType";

		$param = array();		
	
		try {
			$request = $layer->getWMSFeatureRequest( $param );		
		}
		catch(OLLayer_Exception $e) {
			return;
		}
		$this->assertTrue(false,"Exception expected but hasn't been thrown.");
	}
	
	function testGetWMSFeatureRequest_invalidLayer_test1() {		
		$layer = new OLLayer();
		$layer->ogc_name = "featureType";

		$param = array();		
		$param['x'] = "123";
		$param['y'] = "233";
		$param['WIDTH'] = "500";
		$param['HEIGHT'] = "350";		
		try {
			$request = $layer->getWMSFeatureRequest( $param );		
		}
		catch(OLLayer_Exception $e) {
			$this->assertEquals($e->getMessage(),"Parameter missing: BBOX");
			return;
		}
		$this->assertTrue(false,"Exception expected but hasn't been thrown.");
	}

	function testGetWMSFeatureRequest_invalidLayer_test2() {		
		$layer = new OLLayer();
		$layer->ogc_name = "featureType";

		$param = array();		
		$param['BBOX'] = "10,12,20,22";
		$param['y'] = "233";
		$param['WIDTH'] = "500";
		$param['HEIGHT'] = "350";
		
		try {
			$request = $layer->getWMSFeatureRequest( $param );		
		}
		catch(OLLayer_Exception $e) {
			$this->assertEquals($e->getMessage(),"Parameter missing: x");
			return;
		}
		$this->assertTrue(false,"Exception expected but hasn't been thrown.");
	}

	function testGetWMSFeatureRequest_invalidLayer_test3() {		
		$layer = new OLLayer();
		$layer->ogc_name = "featureType";

		$param = array();		
		$param['BBOX'] = "10,12,20,22";
		$param['x'] = "123";
		$param['WIDTH'] = "500";
		$param['HEIGHT'] = "350";
		
		try {
			$request = $layer->getWMSFeatureRequest( $param );		
		}
		catch(OLLayer_Exception $e) {
			$this->assertEquals($e->getMessage(),"Parameter missing: y");
			return;
		}
		$this->assertTrue(false,"Exception expected but hasn't been thrown.");
	}

	function testGetWMSFeatureRequest_invalidLayer_test4() {		
		$layer = new OLLayer();
		$layer->ogc_name = "featureType";

		$param = array();		
		$param['BBOX'] = "10,12,20,22";
		$param['x'] = "123";
		$param['y'] = "233";
		$param['HEIGHT'] = "350";
		
		try {
			$request = $layer->getWMSFeatureRequest( $param );		
		}
		catch(OLLayer_Exception $e) {
			$this->assertEquals($e->getMessage(),"Parameter missing: WIDTH");
			return;
		}
		$this->assertTrue(false,"Exception expected but hasn't been thrown.");
	}

	function testGetWMSFeatureRequest_invalidLayer_test5() {		
		$layer = new OLLayer();
		$layer->ogc_name = "featureType";

		$param = array();		
		$param['BBOX'] = "10,12,20,22";
		$param['x'] = "123";
		$param['y'] = "233";
		$param['WIDTH'] = "500";
		
		try {
			$request = $layer->getWMSFeatureRequest( $param );		
		}
		catch(OLLayer_Exception $e) {
			$this->assertEquals($e->getMessage(),"Parameter missing: HEIGHT");
			return;
		}
		$this->assertTrue(false,"Exception expected but hasn't been thrown.");
	}

	function testGetWMSFeatureRequest_invalidLayer_test6() {		
		$layer = new OLLayer();

		$param = array();		
		$param['BBOX'] = "10,12,20,22";
		$param['x'] = "123";
		$param['y'] = "233";
		$param['WIDTH'] = "500";
		$param['HEIGHT'] = "350";
		
		try {
			$request = $layer->getWMSFeatureRequest( $param );		
		}
		catch(OLLayer_Exception $e) {
			$this->assertEquals($e->getMessage(),"Feature type of the layer is not defined.");
			return;
		}
		$this->assertTrue(false,"Exception expected but hasn't been thrown.");
	}


	/**
	 * Test WMS request without mapname
	 */
	function testGetWMSFeatureRequest_no_map() {
		
		$layer = new OLLayer();
		$layer->ogc_name = "featureType";

		$param = array();		
		$param['BBOX'] = "10,12,20,22";
		$param['x'] = "123";
		$param['y'] = "233";
		$param['WIDTH'] = "500";
		$param['HEIGHT'] = "350";
		
		$request = $layer->getWMSFeatureRequest( $param );		
		$this->assertEquals($request,"?REQUEST=GetFeatureInfo&INFO_FORMAT=application/vnd.ogc.gml&VERSION=1.1.1&TRANSPARENT=true&STYLE=&EXCEPTIONS=application/vnd.ogc.se_xml&FORMAT=image/png&SRS=EPSG%3A4326&LAYERS=featureType&QUERY_LAYERS=featureType&BBOX=10,12,20,22&x=123&y=233&WIDTH=500&HEIGHT=350");
	}

	/**
	 * Test WMS request without mapname
	 */
	function testGetWMSFeatureRequest_map() {
		
		$layer = new OLLayer();
		$layer->ogc_name = "featureType";
		$layer->ogc_map  = "TestMap";

		$param = array();		
		$param['BBOX'] = "10,12,20,22";
		$param['x'] = "123";
		$param['y'] = "233";
		$param['WIDTH'] = "500";
		$param['HEIGHT'] = "350";
		
		$request = $layer->getWMSFeatureRequest( $param );		
		$this->assertEquals($request,"?map=TestMap&REQUEST=GetFeatureInfo&INFO_FORMAT=application/vnd.ogc.gml&VERSION=1.1.1&TRANSPARENT=true&STYLE=&EXCEPTIONS=application/vnd.ogc.se_xml&FORMAT=image/png&SRS=EPSG%3A4326&LAYERS=featureType&QUERY_LAYERS=featureType&BBOX=10,12,20,22&x=123&y=233&WIDTH=500&HEIGHT=350");
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

	function testGetFeatureInfo() {
	}

/*
** Parameter structure:
* 		$param['BBOX']
*		$param['WIDTH']
* 		$param['HEIGHT']
* 		$param['x']
* 		$param['z']/
	function testSendWMSFeatureRequest() {
		
		
	}
	
	function testSendWFSFeatureRequest() {
	}	
	
	function testGetFeatureInfo() {
	}
	
	*/
}
