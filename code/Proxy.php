<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage code
 */

/**
 * Proxy class which is used to manage WFS requests.
 *
 */
class Proxy extends Page {
	
	public static $db = array(
	);	

	function getCMSFields() {
		$fields = parent::getCMSFields();

		// return the modified fieldset.
		return $fields;
	}

}

/**
 * Proxy controller class which delegates requests to the allowed domains.
 */
class Proxy_Controller extends Controller {
	
	public function init() {
		parent::init();
	}
	
	public function dorequest($data) {

		$vars = $data->getVars();
		
		$url = $vars['u'];
		$isPost = $data->isPOST();
				
		// Open the Curl session
		$session = curl_init($url);

		// If it's a POST, put the POST data in the body
		if ($data->isPOST()) {
			$postvars = '';
			$vars = $data->postVars();
			foreach($vars as $k -> $v) {
				$postvars .= $k.'='.$v.'&';
			}
			curl_setopt ($session, CURLOPT_POST, true);
			curl_setopt ($session, CURLOPT_POSTFIELDS, $postvars);
		}

		// Don't return HTTP headers. Do return the contents of the call
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

		// Make the call
		$xml = curl_exec($session);

		// The web service returns XML. Set the Content-Type appropriately
		header("Content-Type: text/xml");

		curl_close($session);

		return $xml;
	}
}