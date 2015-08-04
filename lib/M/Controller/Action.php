<?php

class M_Controller_Action extends Zend_Controller_Action {
	protected static $_inited = false;
	
	public function init() {
		if (self::$_inited === false) {
			$this->view->config = Zend_Registry::get('config');
			$this->view->doctype('<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">');
			$this->view->headMeta()->appendName('description', 'yadda. yet another daily deal aggregator.');
			$this->view->headMeta()->appendName('keywords', 'daily deal, group buying, coupon, south africa, cape town, johannesburg, durban, pretoria');
			$this->view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8');
			$this->view->headMeta()->appendName('viewport', 'width=device-width,minimum-scale=1.0,maximum-scale=1.0');
			$this->view->headLink()->appendStylesheet('/css/main.css');
			$this->view->addHelperPath(APPLICATION_BASE.'/modules/www/views/helpers', 'Www_View_Helper_');
			$this->view->addHelperPath(APPLICATION_BASE.'/modules/m/views/helpers', 'M_View_Helper_');
			$this->view->flashMessages = $this->getHelper('FlashMessenger')->getMessages();
			self::$_inited = true;
		}
	}
}