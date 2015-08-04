<?php

class Admin_Controller_Action_Helper_ReturnUrl extends Zend_Controller_Action_Helper_Abstract {
	public function __construct() {
		if (isset($_GET['return']) && preg_match('#^/#', $_GET['return'])) {
			$session = new Zend_Session_Namespace('ReturnUrl');
			$session->url = $_GET['return'];
		}
	}
	
	public function getUrl($default) {
		$return = $default;
		$session = new Zend_Session_Namespace('ReturnUrl');
		if ($session->url !== null) {
			$return = $session->url;
			$session->url = null;
		}
		return $return;
	}
}