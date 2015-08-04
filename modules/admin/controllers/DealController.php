<?php

class DealController extends Admin_Controller_Action {
	public function listAction() {
		$params = array_merge(
			array('count' => 30),
			array_intersect_key($_GET, Yadda_Model_Deal::$allowedSearchParams)
		);
		$this->view->deals = Yadda_Model_Deal::search($params);
	}
	
	public function editAction() {
		$id = $this->_getParam('id');
		$this->view->deal = Yadda_Model_Deal::find($id);
		$form = new Admin_Form_Deal_Edit($this->view->deal);
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				try {
					Yadda_Model_Deal::update($id, $values);
					$this->getHelper('FlashMessenger')->addMessage('Deal updated.');
					$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/deal/edit/id/'.$id));
				} catch (Yadda_Model_Exception $e) {
					$this->view->flashMessages[] = 'Update failed: '.$e->getMessage();
				}
			}
		} else {
			$form->populate($this->view->deal);
		}
		$this->view->form = $form;
	}
	
	public function deleteAction() {
		$id = $this->_getParam('id');
		try {
			Yadda_Model_Deal::delete($id);
			$this->getHelper('FlashMessenger')->addMessage('Deal deleted.');
		} catch (Yadda_Model_Exception $e) {
			$this->getHelper('FlashMessenger')->addMessage('Error: '.$e->getMessage());
		}
		$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/deal/list'));
	}
	
	public function featureAction() {
		$id = $this->_getParam('id');
		try {
			Yadda_Model_Deal::feature($id);
			$this->getHelper('FlashMessenger')->addMessage('Deal featured.');
		} catch (Yadda_Model_Exception $e) {
			$this->getHelper('FlashMessenger')->addMessage('Error: '.$e->getMessage());
		}
		$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/deal/list'));
	}
}