<?php

class M_Form_Contact extends Zend_Form {
	public function __construct() {
		parent::__construct();
	
		$this->addElements(array(
			'name' => array(
				'type' => 'text'
			),
			'email' => array(
				'type' => 'text'
			),
			'comments' => array(
				'type' => 'textarea'
			)
		));
	}
}