<?php

class Admin_Controller_Action extends Zend_Controller_Action {
	protected static $_inited = false;
	
	public function init() {
		if (self::$_inited === false) {
			self::$_inited = true;
			
			$this->view->config = Zend_Registry::get('config');
			
			Zend_Controller_Action_HelperBroker::addHelper(new Admin_Controller_Action_Helper_ReturnUrl());
			
			$this->view->doctype(Zend_View_Helper_Doctype::XHTML5);
			$this->view->headTitle('yadda. - Admin');
			$this->view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8');
			$this->view->headLink()->appendStylesheet('/css/admin.css');
		}
	}
	
	public function preDispatch() {
		$helper = $this->getHelper('FlashMessenger');
		$this->view->flashMessages = $helper->getMessages();
	}
}