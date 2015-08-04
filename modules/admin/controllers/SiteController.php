<?php

class SiteController extends Admin_Controller_Action {
	public function listAction() {
		$params = array_merge(
			array('count' => 30),
			array_intersect_key($_GET, Yadda_Model_Site::$allowedSearchParams)
		);
		$this->view->sites = Yadda_Model_Site::search($params);
	}
	
	public function newAction() {
		$form = new Admin_Form_Site_New();
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				try {
					$id = Yadda_Model_Site::create($values);
					$this->getHelper('FlashMessenger')->addMessage('Site created.');
					$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/site/edit/id/'.$id));
				} catch (Yadda_Model_Exception $e) {
					$this->view->flashMessages[] = 'Error: '.$e->getMessage();
				}
			}
		}
		$this->view->form = $form;
	}
	
	public function editAction() {
		$id = $this->_getParam('id');
		$this->view->site = Yadda_Model_Site::find($id);
		$form = new Admin_Form_Site_Edit($this->view->site);
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				try {
					Yadda_Model_Site::update($id, $values);
					$this->getHelper('FlashMessenger')->addMessage('Site updated.');
					$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/site/edit/id/'.$id));
				} catch (Yadda_Model_Exception $e) {
					$this->view->flashMessages[] = 'Update failed: '.$e->getMessage();
				}
			}
		} else {
			$form->populate($this->view->site);
		}
		$this->view->form = $form;
		
		// get feeds
		$this->view->feeds = Yadda_Model_Feed::all(array('site' => $this->view->site['id']));
	}
	
	public function deleteAction() {
		$id = $this->_getParam('id');
		try {
			Yadda_Model_Site::delete($id);
			$this->getHelper('FlashMessenger')->addMessage('Site deleted.');
		} catch (Yadda_Model_Exception $e) {
			$this->getHelper('FlashMessenger')->addMessage('Error: '.$e->getMessage());
		}
		$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/site/edit/id/'.$id));
	}
}