<?php

class M_Controller_Router extends Zend_Controller_Router_Rewrite {
	public function __construct() {
		parent::__construct();
		$this->removeDefaultRoutes();
		$this->addRoute('home', new Zend_Controller_Router_Route('', array('controller' => 'index', 'action' => 'index')));
		$this->addRoute('search', new Zend_Controller_Router_Route('search', array('controller' => 'deal', 'action' => 'search')));
		$this->addRoute('deal', new Zend_Controller_Router_Route('deal/:id', array('controller' => 'deal', 'action' => 'view'), array('id' => '\d+')));
		$this->addRoute('contact', new Zend_Controller_Router_Route('contact', array('controller' => 'index', 'action' => 'contact')));
	}
}