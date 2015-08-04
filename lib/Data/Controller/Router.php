<?php

class Data_Controller_Router extends Zend_Controller_Router_Rewrite {
	public function __construct() {
		parent::__construct();
		$this->removeDefaultRoutes();
		
		$this->addRoute('deal-listing', new Zend_Controller_Router_Route('deal/:id/listing.jpg', array('controller' => 'deal', 'action' => 'thumb', 'width' => 75, 'height' => 75, 'crop' => false, 'filename' => 'listing.jpg'), array('id' => '\d+')));
		$this->addRoute('deal-detail', new Zend_Controller_Router_Route('deal/:id/detail.jpg', array('controller' => 'deal', 'action' => 'thumb', 'width' => 250, 'height' => 250, 'crop' => false, 'filename' => 'detail.jpg'), array('id' => '\d+')));
	}
}