<?php

class ErrorController extends Www_Controller_Action {
	public function errorAction() {
		$this->view->headTitle(' - Error ', 'APPEND');
		$this->view->exception = $this->_getParam('error_handler')->exception;
		
		switch (get_class($this->view->exception)) {
			case 'Zend_Controller_Router_Exception':
				$this->getResponse()->setHttpResponseCode(404);
				break;
				
			default:
				$this->getResponse()->setHttpResponseCode(500);
				break;
		}
		
		$this->view->headLink()->appendStylesheet('/css/error/error.css');
		
		if (
			APPLICATION_ENVIRONMENT == 'production' &&
			get_class($this->view->exception) != 'Zend_Controller_Router_Exception'
		) {
			
			// mail the error to neil@yadda.co.za
			$mail = new Zend_Mail('utf-8');
			$mail->setFrom('no-reply@yadda.co.za', 'yadda.');
			$mail->setSubject('Exception at '.$_SERVER['REQUEST_URI']);
			$mail->addTo('neil@yadda.co.za');
			
			ob_start();
			var_dump($this->view->exception);
			echo "\n\n";
			var_dump($_SERVER);
			$mail->setBodyText(ob_get_clean());
			
			try {
				$mail->send();
			} catch (Exception $e) {
				// ignore
			}
		}
	}
}