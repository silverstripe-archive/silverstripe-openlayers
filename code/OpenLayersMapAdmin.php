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
	
	private static $menu_title = "OpenLayers";
	
	private static $url_segment = "openlayers";

	// ##/##
	// static $collection_controller_class = "OpenLayersMapAdmin_CollectionController";
	
	// ##/##
	// static $record_controller_class = "OpenLayersMapAdmin_RecordController";
	
	private static $managed_models = array(
		"OLMapObject",
		"OLLayer",
		"OLStyleMap"
	);
	
	private static $allowed_actions = array(
	);
}

// ##/##
// /**
//  * OpenLayers - ModelAdmin_CollectionController class
//  *
//  * Handles managed product classes and provides default collection filtering behavior.
//  */
// class OpenLayersMapAdmin_CollectionController extends ModelAdmin_CollectionController {

// 	/**
// 	 * Creates and returns the result table field for resultsForm.
// 	 * Uses {@link resultsTableClassName()} to initialise the formfield. 
// 	 * Method is called from {@link ResultsForm}.
// 	 *
// 	 * @param array $searchCriteria passed through from ResultsForm 
// 	 *
//      * @codeCoverageIgnore
// 	 *
// 	 * @return TableListField 
// 	 */
// 	function getResultsTable($searchCriteria) {
// 		$tf = parent::getResultsTable($searchCriteria);
// 		$tf->setFieldCasting(
// 			array(
// 				'Enabled' => 'Boolean->Nice',
// 				'Visible' => 'Boolean->Nice',
// 				'Queryable' => 'Boolean->Nice',
// 				'Cluster' => 'Boolean->Nice',
// 				'ogc_transparent' => 'Boolean->Nice'
// 			));
				
// 		return $tf;
// 	}	
// }

// class OpenLayersMapAdmin_RecordController extends ModelAdmin_RecordController {
	
// 	/**
// 	 * Handler: server side implementation of the 'describe feature type' button 
// 	 * in the CMS. The method returns a JSON string with a list of all labels of a
// 	 * WFS layer. 
// 	 * If the layer is not setup correctly or this layer is a WMS layer, it returns a 
// 	 * Message string as a JSON string.
// 	 *
// 	 */
// 	function describeFeature($request) {
		
// 		$layer = $this->currentRecord;
		
// 		if(!$layer->canCreate(Member::currentUser())) return false;

// 		try {
// 			$result = $layer->describeFeatureType();
// 		} 
// 		catch(Exception $e) {
// 			$result[] = 'An unexpected server error occurred. Please try again.';
// 		}
		
// 		if (count($result) == 0) {
// 			$result[] = 'No attributes found via WFS interface. Please verify: <br/><ol><li>the parameters are correct and</li><li>this layer is a WFS layer.</li></ol>';
// 		}
		
// 		$result = json_encode($result);
// 		return $result;
// 	}
// }