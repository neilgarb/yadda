<?php

class Data_Controller_Action extends Zend_Controller_Action {
	protected static $_inited = false;
	
	public function init() {
		parent::init();
		
		if (self::$_inited === false) {
			self::$_inited = true;
			
			$this->view->doctype(Zend_View_Helper_Doctype::XHTML5);
			$this->view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8');
		}
	}
}