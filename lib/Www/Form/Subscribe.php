<?php

class Www_Form_Subscribe extends Zend_Form {
	public function __construct() {
		parent::__construct();
		
		$router = new Www_Controller_Router();
		$this->setAction($router->assemble(array(), 'subscribe'));
		
		$times = array();
		for ($i = 0; $i < 24; $i ++) {
			$times[$i] = sprintf('%02d:00', $i).' GMT';
		}
		
		$this->addElements(array(
			'email' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Your email address',
					'placeholder' => 'Your email address',
					'decorators' => array('ViewHelper')
				)
			),
			'time' => array(
				'type' => 'select',
				'options' => array(
					'label' => 'When would you like to receive your email updates?',
					'multiOptions' => $times,
					'value' => ((int) gmdate('H') + 1) % 24,
					'decorators' => array('ViewHelper')
				),
				
			),
			'name' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Your name',
					'placeholder' => 'Your name',
					'decorators' => array('ViewHelper')
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
		
		foreach (Yadda_Model_Deal::$allowedSearchParams as $key => $value) {
			$this->addElement('hidden', $key, array(
				'decorators' => array('ViewHelper')
			));
		}
	}
}