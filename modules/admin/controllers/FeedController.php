<?php

class FeedController extends Admin_Controller_Action {
	public function newAction() {
		$form = new Admin_Form_Feed_New();
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				try {
					$id = Yadda_Model_Feed::create($values);
					$this->getHelper('FlashMessenger')->addMessage('Feed created.');
					$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/feed/edit/id/'.$id));
				} catch (Yadda_Model_Exception $e) {
					$this->view->flashMessages[] = 'Error: '.$e->getMessage();
				}
			}
		} else {
			if (isset($_GET['site'])) {
				$form->populate(array('site' => $_GET['site']));
			}
		}
		$this->view->form = $form;
	}
	
	public function editAction() {
		$feed = Yadda_Model_Feed::find($this->_getParam('id'));
		$form = new Admin_Form_Feed_Edit($feed);
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				try {
					Yadda_Model_Feed::update($feed['id'], $values);
					$this->getHelper('FlashMessenger')->addMessage('Feed updated.');
					$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/feed/edit/id/'.$feed['id']));
				} catch (Yadda_Model_Exception $e) {
					$this->view->flashMessages[] = 'Error: '.$e->getMessage();
				}
			}
		} else {
			$form->populate($feed);
		}
		$this->view->form = $form;
	}
	
	public function deleteAction() {
		$id = (int) $this->_getParam('id');
		try {
			Yadda_Model_Feed::delete($id);
			$this->getHelper('FlashMessenger')->addMessage('Feed deleted.');
		} catch (Yadda_Model_Exception $e) {
			$this->getHelper('FlashMessenger')->addMessage('Error: '.$e->getMessage());
		}
		$this->_redirect($this->getHelper('ReturnUrl')->getUrl('/feed/edit/id/'.$id));
	}
}