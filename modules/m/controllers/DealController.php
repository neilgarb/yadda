<?php

class DealController extends M_Controller_Action {
	public function searchAction() {
		$params = array_merge(
			array('page' => 1),
			array_intersect_key($_GET, Yadda_Model_Deal::$allowedSearchParams),
			array('count' => 5)
		);
		$this->view->results = Yadda_Model_Deal::search($params);
		$this->view->headTitle('yadda. - '.$this->view->results['description'], 'SET');
		$this->view->regions = Yadda_Model_Region::index(false);
		$this->view->priceRanges = Yadda_Model_Deal::$priceRanges;
	}
	
	public function viewAction() {
		$id = (int) $this->_getParam('id');
		$this->view->deal = Yadda_Model_Deal::find($id);
		$this->view->headTitle('yadda. - '.$this->view->deal['title'], 'SET');
		$this->view->headMeta()->setName('description', $this->view->trim($this->view->deal['description'], 160));
		$this->view->canonical = 'http://'.$this->view->config->domain->www.'/deal/'.$this->view->deal['id'];
	}
}
