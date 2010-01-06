<?php
/**
 * Proxy controller class which delegates requests to the allowed domains.
 */
class Proxy_Controller extends Controller {

	protected static $allowed_host = array('202.36.29.39');

	/**
	 * Sets the array of allowed hosts.
	 *
	 * @param array $value string array of allowed hosts, i.e. IP addresses.
	 */
	static function set_allowed_host($value) {
		self::$allowed_host = $value;
	}

	/**
	 * Return the array of allowed hosts.
	 *
	 * @return array list of all allowed hosts
	 */
	static function get_allowed_host() {
		return self::$allowed_host;
	}
	
	/**
	 * This method passes through a HTTP request get request to another 
	 * webserver. This proxy is used to avoid any cross domain issues.
	 *
	 * @param SS_HTTPRequest $data array of parameters
	 *
	 * $data['u']:         URL (complete request string)
	 * $data['no_header']: set to '1' to avoid sending header information 
	 *                     directly. 
	 * @return the CURL response
	 */
	public function dorequest($data) {		

		$headers   = array();
		$vars      = $data->getVars();
		$no_header = false;
		
		if (!isset($vars['u'])) {
			return "Invalid request.";
		}
		$url     = $vars['u'];

		if (isset($vars['no_header']) && $vars['no_header'] == '1') {
			$no_header = true;
		}
		
		$checkUrl = explode("/",$url);
		if(!in_array($checkUrl[2],self::get_allowed_host())) {
			return "Access denied to ($url).";
		}
		
		// Open the Curl session
		$session = curl_init($url);

		// If it's a POST, put the POST data in the body
		$isPost = $data->isPOST();
		if ($isPost) {
			$postvars = '';
			$vars = $data->getBody();
			
			if ($vars) {
				$postvars = "body=".$vars;
			} else {
				$vars = $data->postVars();

				if ($vars) {
					foreach($vars as $k => $v) {
						$postvars .= $k.'='.$v.'&';
					}
				}
			}
			
			$headers[] = 'Content-type: text/xml';
			curl_setopt ($session, CURLOPT_HTTPHEADER, $headers); 
			
			curl_setopt ($session, CURLOPT_POST, true);
			curl_setopt ($session, CURLOPT_POSTFIELDS, $postvars);
		}

		// Don't return HTTP headers. Do return the contents of the call
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

		// Make the call
		$xml = curl_exec($session);

		// The web service returns XML. Set the Content-Type appropriately
		if ($no_header == false) {
			header("Content-Type: text/xml");
		}
		curl_close($session);
		return $xml;
	}
}

class Proxy_Controller_Exception extends Exception {}