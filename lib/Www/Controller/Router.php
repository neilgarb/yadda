<?php

class Www_Controller_Router extends Zend_Controller_Router_Rewrite {
	public function __construct() {
		parent::__construct();
		
		$this->removeDefaultRoutes();
		$this->addRoute('home', new Zend_Controller_Router_Route('', array('controller' => 'index', 'action' => 'index')));
		$this->addRoute('deal', new Zend_Controller_Router_Route('deal/:id', array('controller' => 'deal', 'action' => 'view')), array('id' => '\d+'));
		$this->addRoute('deal/vote', new Zend_Controller_Router_Route('vote/:id', array('controller' => 'deal', 'action' => 'vote')), array('id' => '\d+'));
		$this->addRoute('search', new Zend_Controller_Router_Route('search', array('controller' => 'deal', 'action' => 'search')));
		$this->addRoute('rss', new Zend_Controller_Router_Route('deals.rss', array('controller' => 'deal', 'action' => 'rss')));
		$this->addRoute('map', new Zend_Controller_Router_Route('map', array('controller' => 'map', 'action' => 'index')));
		$this->addRoute('mailer', new Zend_Controller_Router_Route('mailer', array('controller' => 'deal', 'action' => 'mailer')));
		$this->addRoute('subscribe', new Zend_Controller_Router_Route('subscribe', array('controller' => 'subscription', 'action' => 'subscribe')));
		$this->addRoute('unsubscribe', new Zend_Controller_Router_Route('unsubscribe', array('controller' => 'subscription', 'action' => 'unsubscribe')));
		$this->addRoute('contact', new Zend_Controller_Router_Route('contact', array('controller' => 'index', 'action' => 'contact')));
		$this->addRoute('ajax/deal/search', new Zend_Controller_Router_Route('ajax/deal/search', array('controller' => 'deal', 'action' => 'ajax-search')));
		$this->addRoute('ajax/map/findDealsByMapBounds', new Zend_Controller_Router_Route('ajax/map/findDealsByMapBounds', array('controller' => 'map', 'action' => 'ajax-find-deals-by-map-bounds')));
		$this->addRoute('sitemap', new Zend_Controller_Router_Route('sitemap.xml', array('controller' => 'index', 'action' => 'sitemap')));
		$this->addRoute('bot', new Zend_Controller_Router_Route('bot', array('controller' => 'index', 'action' => 'bot')));
	}
}