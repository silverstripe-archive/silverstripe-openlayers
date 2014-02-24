<?php
/**
 * Created by PhpStorm.
 * User: rainer
 * Date: 12/02/14
 * Time: 12:07 PM
 */

class WFS_Controller extends Controller {

    private static $allowed_actions = array(
        'describeFeature'
    );

    public function describeFeature($request) {
        $parameters = $request->allParams();
        $layerID = $parameters['ID'];

        $layer = DataObject::get_by_id('OLLayer',$layerID);

        if (!$layer->canCreate(Member::currentUser())) {
            return false;
        }
        try {
            $result = $layer->describeFeatureType();
        }
 		catch(Exception $e) {
 			$result[] = 'An unexpected server error occurred. Please try again.';
 		}

 		if (count($result) == 0) {
 			$result[] = 'No attributes found via WFS interface. Please verify: <br/><ol><li>the parameters are correct and</li><li>this layer is a WFS layer.</li></ol>';
 		}
 		$result = json_encode($result);
 		return $result;
    }

}