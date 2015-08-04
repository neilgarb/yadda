<?php

class ErrorController extends Admin_Controller_Action {
	public function errorAction() {
		$this->view->exception = $this->_getParam('error_handler')->exception;
	}
}