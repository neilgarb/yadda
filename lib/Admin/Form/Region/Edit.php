<?php

class Admin_Form_Region_Edit extends Admin_Form_Region_New {
	public function __construct($region) {
		parent::__construct();
		
		$this->setAction('/region/edit/id/'.$region['id']);
		
		$this->id->setAttrib('readonly', 'readonly');
	}
}