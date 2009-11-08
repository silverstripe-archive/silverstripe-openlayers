<?php

/**
 * 
 */
class OLMapProperty extends DataObject {
	
	static $db = array(
		'Name' => 'Varchar(255)',
		'Value' => 'Varchar(255)'
	);
	
	static $has_one = array(
		'Map' => 'OLMapPage'
	);
	
}
?>