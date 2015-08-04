<?php

class RegionController extends Admin_Controller_Action {
	public function listAction() {
		$params = array_merge(
			array('count' => 30),
			array_intersect_key($_GET, Yadda_Model_Region::$allowedSearchParams)
		);
		$this->view->regions = Yadda_Model_Region::search($params);
	}
	
	public function newAction() {
		$form = new Admin_Form_Region_New();
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				try {
					$id = Yadda_Model_Region::create($values);
					$this->getHelper('FlashMessenger')->addMessage('Region created.');
					$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/region/edit/id/'.$id));
				} catch (Yadda_Model_Exception $e) {
					$this->view->flashMessages[] = 'Error: '.$e->getMessage();
				}
			}
		}
		$this->view->form = $form;
	}
	
	public function editAction() {
		$id = $this->_getParam('id');
		$this->view->region = Yadda_Model_Region::find($id);
		$form = new Admin_Form_Region_Edit($this->view->region);
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				try {
					Yadda_Model_Region::update($id, $values);
					$this->getHelper('FlashMessenger')->addMessage('Region updated.');
					$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/region/edit/id/'.$id));
				} catch (Yadda_Model_Exception $e) {
					$this->view->flashMessages[] = 'Update failed: '.$e->getMessage();
				}
			}
		} else {
			$form->populate($this->view->region);
		}
		$this->view->form = $form;
	}
	
	public function deleteAction() {
		$id = $this->_getParam('id');
		try {
			Yadda_Model_Region::delete($id);
			$this->getHelper('FlashMessenger')->addMessage('Region deleted.');
		} catch (Yadda_Model_Exception $e) {
			$this->getHelper('FlashMessenger')->addMessage('Error: '.$e->getMessage());
		}
		$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/region/edit/id/'.$id));
	}
}