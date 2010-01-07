<?php

Director::addRules(100, array(
	'Proxy' => 'Proxy_Controller'
));

Proxy_Controller::set_allowed_host(array('202.36.29.39'));

?>
