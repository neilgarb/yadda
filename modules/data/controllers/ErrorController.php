<?php

class ErrorController extends Data_Controller_Action {
	public function errorAction() {
		$this->view->exception = $this->_getParam('error_handler')->exception;
		
		switch (get_class($this->view->exception)) {
			case 'Data_Exception_Error':
				$this->getResponse()->setHttpResponseCode(500);
				break;
			
			case 'Data_Exception_NotFound':
			default:
				$this->getResponse()->setHttpResponseCode(404);
				break;
		}
		
		// prepare the view
		$this->view->headTitle('Error', 'SET');
	}
}