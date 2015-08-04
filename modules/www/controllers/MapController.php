<?php

class MapController extends Www_Controller_Action {
	public function indexAction() {
		$this->view->headLink()->appendStylesheet('/css/map/index.css');
		$this->view->headScript()->appendFile('http://maps.googleapis.com/maps/api/js?sensor=false');
		$this->view->headScript()->appendFile('https://apis.google.com/js/plusone.js');
		$this->view->headScript()->appendFile('/js/gmaps.clusterer.js');
		$this->view->headTitle('yadda. - Map');
		$this->view->headMeta()->setName('description', 'Browse daily deals from South Africa\'s favourite group buying websites on a map of South Africa.');
		
		$this->view->regions = Yadda_Model_Region::all();
		$this->view->showAds = false;
	}
	
	public function ajaxFindDealsByMapBoundsAction() {
		$bounds = isset($_GET['bounds']) ? explode(',', $_GET['bounds']) : null;
		$deals = Yadda_Model_Deal::allByMapBounds($bounds);
		$return = array();
		foreach ($deals as $deal) {
			$return[] = array(
				'lat' => $deal['lat'],
				'long' => $deal['long'],
				'html' => $this->view->partial('deal/_deal.phtml', array(
					'config' => $this->view->config,
					'deal' => $deal
				))
			);
		}
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(Zend_Json::encode($return));
		$this->getResponse()->sendResponse();
		die;
	}
}