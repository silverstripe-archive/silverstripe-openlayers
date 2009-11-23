<?php
/**
 * Proxy controller class which delegates requests to the allowed domains.
 */
class Proxy_Controller extends Controller {

	protected static $allowedHost = array('202.36.29.39');
	
	public function init() {
		parent::init();
	}
	
	public function dorequest($data) {
		$vars = $data->getVars();
		$headers = array();
		
		$url = $vars['u'];
		
		$checkUrl = explode("/",$url);
		if(!in_array($checkUrl[2],self::$allowedHost)) {
			user_error("This proxy does not allow you to access that location ($url).", E_USER_ERROR);
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
		header("Content-Type: text/xml");

		curl_close($session);
		
		return $xml;
	}
}