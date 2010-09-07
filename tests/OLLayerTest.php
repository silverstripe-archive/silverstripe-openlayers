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
		//$this->assertEquals($options['SSID'], '1');
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
		//$this->assertEquals($options['SSID'], '1');
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

	/**
	 * Test if the OLLayer_Exception class exist.
	 */
	function testLayerException() {
		$exception = new OLLayer_Exception();
		$this->assertTrue(is_a($exception, "OLLayer_Exception"));
	}

	/**
	 * Test the business logic around GetFeatureInfo. This method calls 
	 * getWFS/WMSFeature methods without the require parameters.
	 */
	function testGetFeatureInfo_InvalidParameterType() {
		$layer = new OLLayer();

		$url = Director::absoluteBaseURL() . self::$test_controller;

		$layer->Url = $url;
		$layer->write();

		try {
			$layer->getFeatureInfo('ID.1');
		}
		catch(Exception $e) {
			$this->assertEquals("Invalid request parameter.", $e->getMessage());
			return;
		}
		$this->assertTrue(false,"Exception hasn't been thrown.");
	}	
	

	
	/**
	 * Test the business logic around GetFeatureInfo. This method calls 
	 * getWFS/WMSFeature methods, sends the HTTP request to the OGC server and returns 
	 * the server response.
	 */
	function testGetFeatureInfo_Invalid() {
		$layer = new OLLayer();

		$url = Director::absoluteBaseURL() . self::$test_controller;

		$layer->Url = $url;
		$layer->write();

		try {
			$layer->getFeatureInfo(array('featureID' => 'ID.1'));
		}
		catch(Exception $e) {
			$this->assertEquals("Request type unknown", $e->getMessage());
			return;
		}
		$this->assertTrue(false,"Exception hasn't been thrown.");
	}	
	
	/**
	 * Test the business logic around GetFeatureInfo. This method calls 
	 * getWFS/WMSFeature methods, sends the HTTP request to the OGC server and returns 
	 * the server response.
	 */
	function testGetFeatureInfo_WFS() {

		$layer = new OLLayer();
		$layer->Type     = 'wfs';
		$layer->ogc_name = "featureType";
		$layer->ogc_map  = "TestMap";
		
		$url = Director::absoluteBaseURL() . self::$test_controller;
		
		$layer->Url = $url;
		$layer->write();
		
		$response = $layer->getFeatureInfo(array('featureID' => 'ID.1'));
		$obj = json_decode($response,1);

		// get project-path from the absolute URL
		$baseUrl = array_slice(explode('/',Director::absoluteBaseURL()),3);
		$projectPath = implode("/",$baseUrl);
		
		// verify/assert response
		$this->assertEquals($obj['url'], "/".$projectPath . self::$test_controller);
		$this->assertEquals($obj['map'], "TestMap");
		$this->assertEquals($obj['request'], "getfeature");
		$this->assertEquals($obj['service'], "WFS");
		$this->assertEquals($obj['version'], "1.0.0");
		$this->assertEquals($obj['typename'], "featureType");
		$this->assertEquals($obj['OUTPUTFORMAT'], "gml3");
		$this->assertEquals($obj['featureid'], "featureType.ID.1");
		$this->assertEquals($obj['isget'], true);
	}

	/**
	 * Test the business logic around GetFeatureInfo. This method calls 
	 * getWFS/WMSFeature methods, sends the HTTP request to the OGC server and returns 
	 * the server response.
	 */
	function testGetFeatureInfo_WMS_InvalidParams() {

		$layer = new OLLayer();
		$layer->Type     = 'wms';
		$layer->ogc_name = "featureType";
		$layer->ogc_map  = "TestMap";
		
		$url = Director::absoluteBaseURL() . self::$test_controller;
		
		$layer->Url = $url;
		$layer->write();
		
		// call getFeatureInfo (for a WMS layer) with a incomplete set of
		// parameters. They should all throw execptions, which is tested in this
		// nested try-catch structure.
		try {
			
			// test without any parameters
			$params = array(
			);
			$response = $layer->getFeatureInfo($params);
		}
		catch(Exception $e) {
			$this->assertFalse(strpos($e->getMessage(),"Mandatory parameter is missing:")===false);

			try {
				// test without bbox parameters
				$params = array(
					'BBOX' => 'bbox',
				);
				$response = $layer->getFeatureInfo($params);
			}
			catch(Exception $e) {
				$this->assertFalse(strpos($e->getMessage(),"Mandatory parameter is missing:")===false);

				// test without bbox,x parameters
				try {
					$params = array(
						'BBOX' => 'bbox',
						'x' => 'x',
					);
					$response = $layer->getFeatureInfo($params);
				}
				catch(Exception $e) {
					$this->assertFalse(strpos($e->getMessage(),"Mandatory parameter is missing:")===false);

					// test without bbox,x,y parameters
					try {
						$params = array(
							'BBOX' => 'bbox',
							'x' => 'x',
							'y' => 'y',
						);
						$response = $layer->getFeatureInfo($params);
					}
					catch(Exception $e) {
						$this->assertFalse(strpos($e->getMessage(),"Mandatory parameter is missing:")===false);
						try {
							// test without bbox,x,y,width parameters
							$params = array(
								'BBOX' => 'bbox',
								'x' => 'x',
								'y' => 'y',
								'WIDTH' => 'width',
							);
							$response = $layer->getFeatureInfo($params);
						}
						catch(Exception $e) {
							$this->assertFalse(strpos($e->getMessage(),"Mandatory parameter is missing:")===false);
							return;
						}
						return;
					}
					return;
				}
				return;
			}
			return;
		}
		$this->assertTrue(false,"Expected exception hasn't been thrown.");
	}

	/**
	 * Test the business logic around GetFeatureInfo. This method calls 
	 * getWFS/WMSFeature methods, sends the HTTP request to the OGC server and returns 
	 * the server response.
	 */
	function testGetFeatureInfo_WMS() {

		$layer = new OLLayer();
		$layer->Type     = 'wms';
		$layer->ogc_name = "featureType";
		$layer->ogc_map  = "TestMap";
		
		$url = Director::absoluteBaseURL() . self::$test_controller;
		
		$layer->Url = $url;
		$layer->write();
		
		$params = array(
			'BBOX' => 'bbox',
			'x' => 'x',
			'y' => 'y',
			'WIDTH' => 'width',
			'HEIGHT' => 'height',
		);
		
		$response = $layer->getFeatureInfo($params);
		$obj = json_decode($response,1);

		// get project-path from the absolute URL
		$baseUrl = array_slice(explode('/',Director::absoluteBaseURL()),3);
		$projectPath = implode("/",$baseUrl);
		
		// verify/assert response
		$this->assertEquals($obj['url'], "/".$projectPath . self::$test_controller);
		$this->assertEquals($obj['map'], "TestMap");
		$this->assertEquals($obj['REQUEST'], "GetFeatureInfo");
		$this->assertEquals($obj['INFO_FORMAT'], "application/vnd.ogc.gml");
		$this->assertEquals($obj['VERSION'], "1.1.1");
		$this->assertEquals($obj['TRANSPARENT'], true);
		$this->assertEquals($obj['EXCEPTIONS'], "application/vnd.ogc.se_xml");
		$this->assertEquals($obj['FORMAT'], "image/png");
		$this->assertEquals($obj['SRS'], "EPSG:4326");
		$this->assertEquals($obj['LAYERS'], "featureType");
		$this->assertEquals($obj['QUERY_LAYERS'], "featureType");
		$this->assertEquals($obj['BBOX'], "bbox");
		$this->assertEquals($obj['x'], "x");
		$this->assertEquals($obj['y'], "y");
		$this->assertEquals($obj['WIDTH'], "width");
		$this->assertEquals($obj['HEIGHT'], "height");
		$this->assertEquals($obj['isget'], true);
	}
	
	/**
	 * Test the business logic around GetFeatureInfo. This method calls 
	 * getWFS/WMSFeature methods, sends the HTTP request to the OGC server and returns 
	 * the server response.
	 */
	function testGetFeatureInfo_wmsUntiled() {

		$layer = new OLLayer();
		$layer->Type     = 'wmsUntiled';
		$layer->ogc_name = "featureType";
		$layer->ogc_map  = "TestMap";
		
		$url = Director::absoluteBaseURL() . self::$test_controller;
		
		$layer->Url = $url;
		$layer->write();
		
		$params = array(
			'BBOX' => 'bbox',
			'x' => 'x',
			'y' => 'y',
			'WIDTH' => 'width',
			'HEIGHT' => 'height',
		);
		
		$response = $layer->getFeatureInfo($params);
		$obj = json_decode($response,1);

		// get project-path from the absolute URL
		$baseUrl = array_slice(explode('/',Director::absoluteBaseURL()),3);
		$projectPath = implode("/",$baseUrl);
		
		// verify/assert response
		$this->assertEquals($obj['url'], "/".$projectPath . self::$test_controller);
		$this->assertEquals($obj['map'], "TestMap");
		$this->assertEquals($obj['REQUEST'], "GetFeatureInfo");
		$this->assertEquals($obj['INFO_FORMAT'], "application/vnd.ogc.gml");
		$this->assertEquals($obj['VERSION'], "1.1.1");
		$this->assertEquals($obj['TRANSPARENT'], true);
		$this->assertEquals($obj['EXCEPTIONS'], "application/vnd.ogc.se_xml");
		$this->assertEquals($obj['FORMAT'], "image/png");
		$this->assertEquals($obj['SRS'], "EPSG:4326");
		$this->assertEquals($obj['LAYERS'], "featureType");
		$this->assertEquals($obj['QUERY_LAYERS'], "featureType");
		$this->assertEquals($obj['BBOX'], "bbox");
		$this->assertEquals($obj['x'], "x");
		$this->assertEquals($obj['y'], "y");
		$this->assertEquals($obj['WIDTH'], "width");
		$this->assertEquals($obj['HEIGHT'], "height");
		$this->assertEquals($obj['isget'], true);
	}	

	
	//Test WhiteList
	function testWhiteList(){
		$layer = new OLLayer();
		
		// matching attributes
		$XMlTag = "<ms::attribute>something</ms:attribute>";
		$keywords = "ms::attribute";
		
		$resp = $layer->WhiteList($XMlTag , $keywords);
		$this->assertTrue($resp);
		
		// not matching attributes
		$XMlTag = "<ms::attribute>something</ms:attribute>";
		$keywords = "ms::Theattribute";
		
		$resp = $layer->WhiteList($XMlTag , $keywords);
		$this->assertFalse($resp);
	}

	//Test WhiteListLabels
	function testWhiteListLabels(){
		$layer = new OLLayer();
		$layer->XMLWhitelist = "attribute1,attribute2,attribute3";
		$layer->Labels = "Label1,Label2,Label3";
		
		$label = $layer->WhiteListLabels('attribute1');
		$this->assertEquals($label, "Label1");

		$label = $layer->WhiteListLabels('attribute2');
		$this->assertEquals($label, "Label2");

		$label = $layer->WhiteListLabels('attribute3');
		$this->assertEquals($label, "Label3");

		$label = $layer->WhiteListLabels('attribute4');
		$this->assertEquals($label, "attribute4");
	}

}
