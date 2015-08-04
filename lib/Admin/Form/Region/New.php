<?php

class Admin_Form_Region_New extends Zend_Form {
	public function __construct() {
		parent::__construct();
		
		$this->setAction('/region/new');
		
		$this->addElements(array(
			'id' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'ID'
				)
			),
			'name' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Name'
				)
			),
			'lat' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Latitude',
					'class' => 'short'
				)
			),
			'long' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Longitude',
					'class' => 'short'
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