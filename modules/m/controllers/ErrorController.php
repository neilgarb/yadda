<?php

class ErrorController extends M_Controller_Action {
	public function errorAction() {
		$this->getResponse()->setHttpResponseCode(404);
		$this->view->exception = $this->_getParam('error_handler')->exception;
		$this->view->headTitle('yadda. - Error', 'SET');
	}
}