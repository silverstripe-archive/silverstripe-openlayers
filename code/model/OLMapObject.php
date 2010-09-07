<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage model
 */

/** 
 * OLMapObject class. 
 *
 * Each OpenLayers map instance is stored in the CMS as a OLMapObject 
 * dataobject  and can be linked to OLMapPages {@link OLMapPages}.
 * OLMapObjects stores information about the map configuration, such as
 * default map view and available layers.
 */
class OLMapObject extends DataObject {
	
	public static $singular_name = 'Map';
	
	public static $plural_name = 'Maps';	

	static $db = array(
		'Title'       => 'Varchar(25)',
		'Description' => 'Varchar(256)',
		'Enabled'     => 'Boolean',

		// map extend settings
		'Resolutions'     => 'Varchar(1024)',
		'Projection'      => 'Varchar(25)',

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

		$fields->removeFieldsFromTab("Root.Main", array("InitLatitude", "InitLongitude", "InitZoom", "Enabled") );
//		$fields->removeFieldsFromTab("Root.Main", array("MinScale","MaxScale", "MaxResolution") );
		$fields->removeFieldsFromTab("Root.Main", array("ExtentLeft", "ExtentBottom", "ExtentRight", "ExtentTop") );
		$fields->removeFieldsFromTab("Root.Main", array("Resolutions", "Projection") );

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
						new LiteralField("MapLabel2","<h3>Extent/Resolution settings</h3>"),
						new TextField("Projection", "Projection"),
						new LiteralField("Projection_example1","<h4>Example for a projection:</h4><p>EPSG:4326</p>"),
						new TextField("Resolutions", "Resolutions"),
						new LiteralField("Resolution_example1","<h4>Example for the Resolutions for EPSG:4326</h4><p>0.703125, 0.3515625, 0.17578125, 0.087890625, 0.0439453125, 0.02197265625, 0.010986328125, 0.0054931640625, 0.00274658203125, 0.001373291015625, 6.866455078125E-4, 3.4332275390625E-4, 1.71661376953125E-4, 8.58306884765625E-5, 4.291534423828125E-5, 2.1457672119140625E-5, 1.0728836059570312E-5, 5.364418029785156E-6, 2.682209014892578E-6, 1.341104507446289E-6, 6.705522537231445E-7, 3.3527612686157227E-7, 1.6763806343078613E-7, 8.381903171539307E-8, 4.190951585769653E-8, 2.0954757928848267E-8, 1.0477378964424133E-8, 5.238689482212067E-9, 2.6193447411060333E-9, 1.3096723705530167E-9, 6.548361852765083E-10</p>"),
						new LiteralField("MapLabel3","<h3>Maximum Map Extent</h3>"),
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
	 * This method serialize the map data structure into an array.
	 *
	 * This method stores relevant map configurations in an array object,
	 * iterate through all associate {@link OLLayers} objects and add those
	 * configurations to the array. This method is called from the templates
	 * via {@link OLMapPage::getDefaultMapConfiguration()} and calls {@link OLLayer::getConfigurationArray()}.
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


		$data['Projection']    = $this->getField('Projection');
		$data['Resolutions']   = explode(",", $this->getField('Resolutions'));

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