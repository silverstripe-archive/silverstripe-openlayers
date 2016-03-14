<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage code
 */

/**
 * Proxy controller delegates HTTP requests to dedicated web servers.
 * To avoid any cross-domain issues within the map application (i.e. requesting
 * XML data from features shown on the map via AJAX calls), we use the
 * proxy controller which delegates requests to the provided URL.
 */
class Proxy_Controller extends Controller
{

    private static $allowed_actions = array(
        'dorequest'
    );

    protected static $allowed_host = array('localhost');

    /**
     * Sets the array of allowed hosts.
     *
     * @codeCoverageIgnore
     *
     * @param array $value string-array of allowed hosts, i.e. IP addresses.
     */
    public static function set_allowed_host($value)
    {
        self::$allowed_host = $value;
    }

    /**
     * Returns an array of allowed hosts.
     *
     * @codeCoverageIgnore
     *
     * @return array list of all allowed hosts
     */
    public static function get_allowed_host()
    {
        return self::$allowed_host;
    }

    /**
     * This method passes through an HTTP request to another webserver.
     * This proxy is used to avoid any cross domain issues. The proxy
     * uses a white-list of domains to minimize security risks.
     *
     * @param SS_HTTPRequest $data array of parameters
     *
     * $data['u']:         URL (complete request string)
     * $data['no_header']: set to '1' to avoid sending header information
     *                     directly.
     * @return the CURL response
     */
    public function dorequest($data)
    {
        $headers   = array();
        $vars      = $data->requestVars();
        $no_header = false;

        if (!isset($vars['u'])) {
            return "Invalid request: unknown proxy destination.";
        }
        $url = $vars['u'];

        if (isset($vars['no_header']) && $vars['no_header'] == '1') {
            $no_header = true;
        }

        $checkUrl = explode("/", $url);

        if (!in_array($checkUrl[2], self::get_allowed_host())) {
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
                $postvars = $vars;
            } else {
                $vars = $data->postVars();
                if ($vars) {
                    foreach ($vars as $k => $v) {
                        $postvars .= $k.'='.$v.'&';
                    }
                }
            }

            $headers[] = 'Content-type: text/xml';
            curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($session, CURLOPT_POST, true);
            curl_setopt($session, CURLOPT_POSTFIELDS, $postvars);
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
