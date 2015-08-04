<?php

class DealController extends Www_Controller_Action {
	public function viewAction() {
		$this->view->deal = Yadda_Model_Deal::find($this->_getParam('id'));
		$this->view->headTitle('yadda. - '.$this->view->deal['title'], 'SET');
		$this->view->title = $this->view->deal['title'];
		$this->view->headLink()->appendStylesheet('/css/deal/view.css');
		$this->view->headMeta()->setName('description', $this->view->trim($this->view->deal['description'], 160));
		$this->view->headScript()->appendFile('https://apis.google.com/js/plusone.js');
		
		/*
		// more like this
		$solr = Yadda_Solr::getInstance();
		$this->view->mlt = $solr->getMoreLikeThis($this->view->deal['id']);
		*/
	}
	
	public function voteAction() {
		$id = (int) $this->_getParam('id');
		try {
			Yadda_Model_Deal::vote($id, Yadda_UserAgent::getIp());
			$this->getHelper('FlashMessenger')->addMessage('Thanks for the vote! We\'ll pass the request on.');
		} catch (Yadda_Model_Exception $e) {
			$this->getHelper('FlashMessenger')->addMessage($e->getMessage());
		}
		$this->_redirect($this->view->url(array('id' => $id), 'deal').'?from=vote');
	}
	
	public function searchAction() {
		$params = array_intersect_key($_GET, Yadda_Model_Deal::$allowedSearchParams);
		
		// data for search form
		$this->view->regions = Yadda_Model_Region::index();
		
		// results
		$result = Yadda_Model_Deal::search(array_merge($params, array('count' => 30)));
		$this->view->deals = $result;
		$this->view->params = $params;
		$title = $this->view->deals['description'];
		$this->view->headTitle('yadda. - '.$title, 'SET');
		$this->view->title = $title;
		
		$this->view->headLink()->appendStylesheet('/css/deal/search.css');
		$this->view->rss = $this->view->url(array(), 'rss').'?'.http_build_query(
			array_diff_key($params, array('count' => null, 'page' => null))
		);
	}
	
	public function rssAction() {
		$params = array_intersect_key($_GET, Yadda_Model_Deal::$allowedSearchParams);
		$result = Yadda_Model_Deal::search(array_merge($params, array('count' => 30)));
		$data = array(
			'title' => 'yadda. - '.$result['description'],
			'link' => 'http://yadda.co.za/',
			'language' => 'en-gb',
			'charset' => 'utf-8',
			'entries' => array()
		);
		foreach ($result['results'] as $result) {
			$desc = $this->view->format($result['description']);
			if (empty($desc)) {
				$desc = '';
			}
			$data['entries'][] = array(
				'title' => $result['title'],
				'description' => $desc,
				'link' => 'http://'.$_SERVER['HTTP_HOST'].$this->view->url(array('id' => $result['id']), 'deal').'?from=rss',
				'guid' => 'http://'.$_SERVER['HTTP_HOST'].$this->view->url(array('id' => $result['id']), 'deal'),
				'lastUpdate' => $result['date'] 
			);
		}
		$feed = Zend_Feed::importArray($data, 'rss');
		$feed->send();
		die;
	}
	
	public function mailerAction() {
		$this->view->params = array_intersect_key($_GET, Yadda_Model_Deal::$allowedSearchParams);
		$this->view->results = Yadda_Model_Deal::search($this->view->params);
		$this->view->layout()->disableLayout();
	}
	
	public function ajaxSearchAction() {
		$params = array_merge(
			array('page' => 1),
			array_intersect_key($_GET, Yadda_Model_Deal::$allowedSearchParams),
			array('count' => 15)
		);
		$deals = Yadda_Model_Deal::search($params);
		$html = array();
		if ($deals['page'] == $deals['params']['page']) {
			foreach ($deals['results'] as $deal) {
				$html[] = $this->view->partial('deal/_deal.phtml', array(
					'config' => $this->view->config,
					'deal' => $deal
				));
			}
		}
		
		$this->getResponse()->setHeader('Content-Type', 'application/json; charset=utf-8');
		$this->getResponse()->setBody(Zend_Json::encode($html));
		$this->getResponse()->sendResponse();
		die;
	}
}