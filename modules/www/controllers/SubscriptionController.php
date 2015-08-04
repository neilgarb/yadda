<?php

class SubscriptionController extends Www_Controller_Action {
	public function subscribeAction() {
		$form = new Www_Form_Subscribe();
		if ($this->getRequest()->isPost()) {
			$this->view->params = array_intersect_key($_POST, Yadda_Model_Deal::$allowedSearchParams);
			$this->view->result = Yadda_Model_Deal::search($this->view->params);
			
			if ($form->isValid($_POST)) {
				
				$values = $form->getValues();
				try {
					Yadda_Model_Subscription::subscribe($values);
					$this->getHelper('FlashMessenger')->addMessage('Your subscription has been set up.');
					$this->_redirect($this->view->url(array(), 'search').'?from=subscribe&'.http_build_query($this->view->params));
				} catch (Yadda_Model_Exception $e) {
					$this->view->flashMessages[] = $e->getMessage();
				}
			}
		} else {
			$this->view->params = array_intersect_key($_GET, Yadda_Model_Deal::$allowedSearchParams);
			$this->view->result = Yadda_Model_Deal::search($this->view->params);
			
			$form->populate($this->view->params);
		}
		$this->view->form = $form;
		$this->view->headLink()->appendStylesheet('/css/subscription/subscribe.css');
	}
	
	public function unsubscribeAction() {
		$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
		$hash = isset($_GET['hash']) ? $_GET['hash'] : null;
		try {
			Yadda_Model_Subscription::unsubscribe($id, $hash);
			$this->getHelper('FlashMessenger')->addMessage('Your unsubscribe request has been processed successfully.');
			$this->_redirect('/?from=unsubscribe');
		} catch (Yadda_Model_Exception $e) {
			$this->view->error = $e->getMessage();
		}
	}
}