<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage code
 */


/**
 * OpenLayers Model Admin class
 *
 * OpenLayers Maps and Layers can be managed in the backend via this ModelAdmin
 * class (see {@link OLLayer} and {@link OLMapObject}.
 */
class OpenLayersMapAdmin extends ModelAdmin {
	
	static $menu_title = "OpenLayers";
	
	static $url_segment = "openlayers";

	static $collection_controller_class = "OpenLayersMapAdmin_CollectionController";
	
	static $managed_models = array(
		"OLMapObject",
		"OLLayer",
		"OLStyleMap"
	);
	
	static $allowed_actions = array(
	);
}

/**
 * OpenLayers - ModelAdmin_CollectionController class
 *
 * Handles managed product classes and provides default collection filtering behavior.
 */
class OpenLayersMapAdmin_CollectionController extends ModelAdmin_CollectionController {

	/**
	 * Creates and returns the result table field for resultsForm.
	 * Uses {@link resultsTableClassName()} to initialise the formfield. 
	 * Method is called from {@link ResultsForm}.
	 *
	 * @param array $searchCriteria passed through from ResultsForm 
	 *
     * @codeCoverageIgnore
	 *
	 * @return TableListField 
	 */
	function getResultsTable($searchCriteria) {
		$tf = parent::getResultsTable($searchCriteria);
		$tf->setFieldCasting(
			array(
				'Enabled' => 'Boolean->Nice',
				'Visible' => 'Boolean->Nice',
				'Queryable' => 'Boolean->Nice',
				'Cluster' => 'Boolean->Nice',
				'ogc_transparent' => 'Boolean->Nice'
			));
				
		return $tf;
	}

}