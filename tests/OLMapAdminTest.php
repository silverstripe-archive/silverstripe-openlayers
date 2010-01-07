<?php

class OLMapAdminTest extends FunctionalTest {

	static $fixture_file = 'openlayers/tests/OLMapAdminTest.yml';
	
	function testModelAdminOpens() {
		$this->autoFollowRedirection = false;
		$this->logInAs('admin');
		$this->assertTrue((bool)Permission::check("ADMIN"));

		$obj = $this->get('/admin/openlayers');
		
		$obj = $this->submitForm('Form_SearchForm_OLMapObject',null);
		$this->assertContains('<a href="admin/openlayers/OLMapObject/1/edit">Map title</a>', $this->content());
		$this->assertContains('<a href="admin/openlayers/OLMapObject/1/edit">Map Description</a>', $this->content());
	}
}
