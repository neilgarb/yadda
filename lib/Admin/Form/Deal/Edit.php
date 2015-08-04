<?php

class Admin_Form_Deal_Edit extends Zend_Form {
	public function __construct($deal) {
		parent::__construct();
		
		$this->setAction('/deal/edit/id/'.$deal['id']);
		
		$this->addElements(array(
			'title' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Title'
				)
			),
			'description' => array(
				'type' => 'textarea',
				'options' => array(
					'label' => 'Description',
					'rows' => 10,
					'cols' => 60
				)
			),
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
			'price' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Price (R)',
					'class' => 'short'
				)
			),
			'value' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Value (R)',
					'class' => 'short'
				)
			),
			'discount' => array(
				'type' => 'text',
				'options' => array(
					'label' => 'Discount (%)',
					'class' => 'short'
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