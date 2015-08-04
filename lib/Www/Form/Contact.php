<?php

class Www_Form_Contact extends Zend_Form {
	public function __construct() {
		parent::__construct();
		
		$router = new Www_Controller_Router();
		$this->setAction($router->assemble(array(), 'contact'));
		
		$this->addElements(array(
			'name' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Your name'
				)
			),
			'email' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Your email address',
					'description' => 'Your email address will not be stored, and will be used solely to respond to your comments.'
				)
			),
			'comments' => array(
				'type' => 'textarea',
				'options' => array(
					'label' => 'Your comments',
					'rows' => 5,
					'cols' => 60
				)
			),
			'submit' => array(
				'type' => 'submit',
				'options' => array(
					'label' => 'Submit',
					'decorators' => array('ViewHelper')
				)
			)
		));
	}
}