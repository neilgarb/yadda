<?php

class Admin_Form_Feed_New extends Zend_Form {
	public function __construct() {
		parent::__construct();
		
		$this->setAction('/feed/new');
		
		$this->addElements(array(
			'site' => array(
				'type' => 'select',
				'options' => array(
					'label' => 'Site',
					'multiOptions' => Yadda_Model_Site::index(true)
				)
			),
			'region' => array(
				'type' => 'select',
				'options' => array(
					'label' => 'Region',
					'multiOptions' => Yadda_Model_Region::index(true)
				)
			),
			'engine' => array(
				'type' => 'select',
				'options' => array(
					'label' => 'Engine',
					'multiOptions' => array('' => '') + Yadda_Model_Feed::$engines
				)
			),
			'url' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'URL',
					'attribs' => array(
						'placeholder' => 'http://'
					)
				)
			),
			'submit' => array(
				'type' => 'submit',
				'options' => array(
					'label' => 'Submit'
				)
			)
		));
	}
}