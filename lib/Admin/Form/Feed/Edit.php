<?php

class Admin_Form_Feed_Edit extends Admin_Form_Feed_New {
	public function __construct($feed) {
		parent::__construct();
		$this->setAction('/feed/edit/id/'.$feed['id']);
	}
}