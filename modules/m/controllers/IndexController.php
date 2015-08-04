<?php

class IndexController extends M_Controller_Action {
	public function indexAction() {
		$this->view->headTitle('yadda. yet another daily deal aggregator.', 'SET');
		$this->view->headMeta()->appendName('description', 'yadda. gathers into one space the daily deals from South Africa\'s favourite websites.');
		$this->view->regions = Yadda_Model_Region::index(false);
		$this->view->priceRanges = Yadda_Model_Deal::$priceRanges;
		$this->view->canonical = 'http://'.$this->view->config->domain->www.'/';
	}
	
	public function contactAction() {
		$form = new M_Form_Contact();
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				try {
					Yadda_Model_Contact::send($values['name'], $values['email'], $values['comments']);
					$this->getHelper('FlashMessenger')->addMessage('Thanks for your comments! We\'ll get back to you shortly.');
					$this->_redirect($this->view->url(array(), 'contact'));
				} catch (Yadda_Model_Exception $e) {
					$this->view->flashMessages[] = $e->getMessage();
				}
			}
		}
		$this->view->form = $form;
		$this->view->headTitle('yadda. - Contact us', 'SET');
		$this->view->canonical = 'http://'.$this->view->config->domain->www.'/contact';
	}
}