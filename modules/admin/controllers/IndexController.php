<?php

class IndexController extends Admin_Controller_Action {
	public function indexAction() {
		$this->view->headLink()->appendStylesheet('/css/index/index.css');
		$this->view->featured = Yadda_Model_Deal::featured(3);

		// fetch report data
		$this->view->reportData = Yadda_Model_Report::pull(
			date('Y-m-d', strtotime('-14 day')),
			date('Y-m-d')
		);
	}
}