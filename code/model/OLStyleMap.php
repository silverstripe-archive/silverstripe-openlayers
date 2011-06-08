<?php

/**
 * 
 *
 * @author Rainer Spittel
 * @version $Id$
 * @copyright SilverStripe Ltd., 2 June, 2011
 * @package default
 **/

/**
 * Define DocBlock
 **/
class OLStyleMap extends DataObject {
	
	static $db = array (
		'Name' => 'Varchar(50)',
		'Default' => 'Text',
		'Select' => 'Text',
		'Temporary' => 'Text'
	);
	
	static $has_many = array(
		"Layers" => "OLLayer"
	);
}