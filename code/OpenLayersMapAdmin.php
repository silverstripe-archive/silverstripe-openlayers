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

	private static $managed_models = array(
		"OLMapObject",
		"OLLayer",
		"OLStyleMap"
	);

	private static $allowed_actions = array(
	);
}

