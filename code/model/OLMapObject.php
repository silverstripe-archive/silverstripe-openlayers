<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage code
 */

/** 
 * OLMapObject class. Each instance of this map class represents a JavaScript
 * OpenLayer Map class. It is used to manage and control the map behavior.
 */
class OLMapObject extends DataObject {
	
	public static $singular_name = 'Map';
	
	public static $plural_name = 'Maps';	

	static $db = array(
		'Title'       => 'Varchar(25)',
		'Description' => 'Varchar(256)',
		'Enabled'     => 'Boolean',

		// map extend settings
		'MinScale'        => 'Int',
		'MaxScale'        => 'Int',
		'MaxResolution'   => 'Varchar(25)',

		// map map extent
		'ExtentLeft'    => 'Decimal',
		'ExtentBottom'    => 'Decimal',
		'ExtentRight'    => 'Decimal',
		'ExtentTop'    => 'Decimal',

		// map default properties
		'InitLatitude'    => 'Decimal',
		'InitLongitude'   => 'Decimal',
		'InitZoom'        => 'Int'
	);	
	
	static $has_many = array(
		'Layers' => 'OLLayer'
	);

	static $summary_fields = array(
	    'Title',
		'Description',
		'Enabled'
	 );

	static $defaults = array(
	    'MinScale'      => 8000000,
	    'MaxScale'      => 10000,
	    'MaxResolution' => 'auto',
	    'Enabled'       => true,
	 );

	static $casting = array(
		'Enabled' => 'Boolean',
	);
	
	static $default_sort = "Title ASC";
	

	/**
	 * Overwrites SiteTree.getCMSFields.
	 *
	 * @return fieldset
	 */
	function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeFieldsFromTab("Root.Main", array("InitLatitude","InitLongitude", "InitZoom","Enabled") );
		$fields->removeFieldsFromTab("Root.Main", array("MinScale","MaxScale", "MaxResolution") );
		$fields->removeFieldsFromTab("Root.Main", array("ExtentLeft","ExtentBottom", "ExtentRight","ExtentTop") );

		$fields->addFieldToTab("Root.Main", new TextField("Title", "Map-Name (Title)"));
		$fields->addFieldToTab("Root.Main", new TextField("Description", "Description"));

		$fields->addFieldsToTab("Root.Main", 
			array(
				new LiteralField("MapLabel","<h2>Default settings</h2>"),
				new CompositeField( 
					new CompositeField( 
						new CheckboxField("Enabled", "Map Enabled"),
						new NumericField("InitLatitude", "Latitude"),
						new NumericField("InitLongitude", "Longitude"),
						new NumericField("InitZoom", "Zoomlevel")
					)
				)
			)
		);
		
		// create a dedicated tab for open layers
		$fields->addFieldsToTab("Root.Configuration", 
			array(
				new LiteralField("MapLabel","<h2>Map settings</h2>"),
				new CompositeField( 
					new CompositeField( 
						new LiteralField("MapLabel","<h3>Extent/Resolution settings</h3>"),
						new NumericField("MinScale", "Min-Scale"),
						new NumericField("MaxScale", "Max-Scale"),
						new TextField("MaxResolution", "Max-Resolution"),
						new LiteralField("MapLabel","<h3>Maximum Map Extent</h3>"),
						new NumericField("ExtentLeft", "Left"),
						new NumericField("ExtentBottom", "Bottom"),
						new NumericField("ExtentRight", "Right"),
						new NumericField("ExtentTop", "Top")
					)
				)
			)
		);
		return $fields;
	}
	
	/**
	 * Serialise the data structure into an array.
	 *
	 * @return array
	 */
	function getConfigurationArray() {
		$config = array();
		$data   = array();
		$extent = array();
		
		// get default settings for this map
		$data['Title']       = $this->getField('Title');
		$data['Latitude']    = $this->getField('InitLatitude');
		$data['Longitude']   = $this->getField('InitLongitude');
		$data['Zoom']        = $this->getField('InitZoom');
		$data['ID']          = $this->getField('ID');

		$data['MinScale']      = $this->getField('MinScale');
		$data['MaxScale']      = $this->getField('MaxScale');
		$data['MaxResolution'] = $this->getField('MaxResolution');

		// set max map extent
		$extent['left']   = $this->getField('ExtentLeft');
		$extent['bottom'] = $this->getField('ExtentBottom');
		$extent['right']  = $this->getField('ExtentRight');
		$extent['top']    = $this->getField('ExtentTop');
		
		$config['Map']          = $data;
		$config['MaxMapExtent'] = $extent;
		
		// get all layers of this map in the order of 'DisplayPriority ASC'
		$layers = $this->getComponents('Layers',null,'DisplayPriority');

		// iterate through the layers and get their configurations
		$data = array();
		if ($layers) {
			foreach($layers as $layer) {
				if ($layer->Enabled == true) {
					$data[] = $layer->getConfigurationArray();
				}
			}
		}
		$config['Layers'] = $data;
		return $config;
	}



}