<?php

class Www_Controller_Action extends Zend_Controller_Action {
	protected static $_inited = false;
	public function init() {
		parent::init();
		
		if (self::$_inited === false) {
			self::$_inited = true;
			
			$this->view->config = Zend_Registry::get('config');
			
			// handle mobi users
			if (Yadda_UserAgent::isMobile()) {
				$session = new Zend_Session_Namespace('mobile');
				if ($session->fromMobile === null) {
					if (isset($_GET['from']) && $_GET['from'] == 'm') {
						// mark that the user wants to be on the standard site
						$session->fromMobile = true;
					} else {
						// redirect if there is an equivalent route
						$router = new M_Controller_Router();
						try { 
							$router->route($this->getRequest());
							// redirect!
							$this->_redirect('http://'.$this->view->config->domain->m.$_SERVER['REQUEST_URI']);
						} catch (Exception $e) {
							// can't redirect!
						}
					}
				} else {
					// user wants to be on the standard site
				}
			}
			
			
			// prepare the view
			$this->view->doctype(Zend_View_Helper_Doctype::XHTML5);
			$this->view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8');
			$this->view->headMeta()->appendName('description', 'yadda. yet another daily deal aggregator.');
			$this->view->headMeta()->appendName('keywords', 'daily deal, group buying, coupon, south africa, cape town, johannesburg, durban, pretoria');
			$this->view->headTitle('yadda. yet another daily deal aggregator.');
			$this->view->headLink()->appendStylesheet('/css/main.css');
			$this->view->headScript()->appendFile('https://apis.google.com/js/plusone.js');
			$this->view->headScript()->appendFile('/js/jquery.js');
			$this->view->headScript()->appendFile('/js/yadda.js');
			$this->view->addHelperPath(APPLICATION_BASE.'/modules/www/views/helpers', 'Www_View_Helper_');
			$this->view->flashMessages = $this->getHelper('FlashMessenger')->getMessages();
			$this->view->rss = $this->view->url(array(), 'rss');
		}
	}
	
	public function postDispatch() {
		$this->view->headLink()->appendAlternate('http://'.$_SERVER['HTTP_HOST'].$this->view->rss, 'application/rss+xml', 'RSS');
	}
}