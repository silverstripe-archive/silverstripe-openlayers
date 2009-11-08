<?php

/**
 * 
 */
class OLLayerProperty extends DataObject {
	
	static $db = array(
		'Name' => 'Varchar(255)',
		'Value' => 'Varchar(255)'
	);
	
	static $has_one = array(
		'Laper' => 'OLLayer'
	);
	
}
?>