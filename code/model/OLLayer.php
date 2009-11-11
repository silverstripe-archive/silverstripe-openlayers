<?php
/** 
 *
 */
class OLLayer extends DataObject {
	
	static $db = array (
		"Name" 				=> "Varchar",
		"Url" 				=> "Varchar(1024)",
		"Type"			  	=> "Enum(array('wms','wfs','wmsUntiled'),'wms')",
		"DisplayPriority" 	=> "Int",
		"Enabled"         	=> "Boolean",
		"Visible"         	=> "Boolean",
		"Queryable"			=> "Boolean",
		
		// temporarily added (will be removed)
		"ogc_name"			=> "Varchar", 		// layer name (ogc layer name/id)
		"ogc_map"			=> "Varchar(1024)",	// url to the map file on the server side
		"ogc_transparent"  => "Boolean"			// transparent overlay layer
	);
	
	static $has_one = array(
		'MapPage' => 'OLMapPage'
	);	
	
	/**
	 * Creates and returns a layer definition array which will be used to configure
	 * open layers on the JavaScript side.
	 *
	 * @return array
	 */
	function serialise() {
		$result = $this->toMap();

		$options = array();
		$options['map']  = $this->getField("ogc_map");
		
		$layerType = $this->getField('Type');
		if ($layerType == 'wms' || $layerType == 'wmsUntiled') {
			$options['layers']       = $this->getField("ogc_name");
			$options['transparent']  = $this->getField("ogc_transparent") ? "true": "false";
		} else 
		if ($layerType == 'wfs') {
			$options['typename']     = $this->getField("ogc_name");			
		}
		$result['Options'] = $options;
		
		return $result;
	}
}