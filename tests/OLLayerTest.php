<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage tests
 */

class OLLayerTest extends SapphireTest {

	static $test_controller = 'ReflectionProxy_Controller/doprocess';
	
	/**
	 * Initiate the controller and page classes and configure GeoNetwork service
	 * to use the mockup-controller for testing.
	 */
	function setUp() {
		parent::setUp();
		
		if(!self::using_temp_db()) self::create_temp_db();
		self::empty_temp_db();
	}

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

	

}
