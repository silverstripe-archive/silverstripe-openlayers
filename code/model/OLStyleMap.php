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
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		// create a dedicated tab for open layers
		$fields->addFieldsToTab("Root.Main", 
			array(
				new LiteralField("StyleMapHelp","<h2>Style Map Help</h2>"),
				new LiteralField("MapLabel1","Please click <a href='map-style-help?stage=Stage' target='_help'>here<a/> for more help.")
			)
		);
		return $fields;
	}
	
	function getStyleMapTemplateName() {
		return 'StyleMap';
	}
}