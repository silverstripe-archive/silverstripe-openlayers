<?php
/**
 * @author Rainer Spittel (rainer at silverstripe dot com)
 * @package openlayers
 * @subpackage tests
 */

class OLMapAdminTest extends FunctionalTest
{

    public static $fixture_file = 'openlayers/tests/OLMapAdminTest.yml';
    
    protected $extraDataObjects = array(
        'ModelAdminTest_Admin',
        'ModelAdminTest_Contact',
    );
    
    public function testModelAdminOpens()
    {
        $this->autoFollowRedirection = false;
        $this->logInAs('admin');
        $this->assertTrue((bool)Permission::check("ADMIN"));

        $obj = $this->get('/admin/openlayers');
        
        $obj = $this->submitForm('Form_SearchForm_OLMapObject', null);
        $this->assertContains('<a href="admin/openlayers/OLMapObject/1/edit">Map title</a>', $this->content());
        $this->assertContains('<a href="admin/openlayers/OLMapObject/1/edit">Map Description</a>', $this->content());
    }
}
