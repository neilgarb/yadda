<?php

class Admin_Form_Site_Edit extends Admin_Form_Site_New {
	public function __construct($site) {
		parent::__construct();
		$this->setAction('/site/edit/id/'.$site['id']);
		$this->id->setAttrib('readonly', 'readonly');
	}
}