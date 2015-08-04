<?php

class Admin_Form_Site_New extends Zend_Form {
	public function __construct() {
		parent::__construct();
		
		$this->setAction('/site/new');
		
		$this->addElements(array(
			'id' => array(
				'name' => 'id',
				'type' => 'text',
				'options' => array(
					'label' => 'ID'
				)
			),
			'name' => array(
				'name' => 'name',
				'type' => 'text',
				'options' => array(
					'label' => 'Name'
				)
			),
			'url' => array(
				'name' => 'url',
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