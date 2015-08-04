<?php

include_once '../bootstrap.php';

Zend_Layout::startMvc();

$front = Zend_Controller_Front::getInstance();
$front
	->setControllerDirectory(array(
		'admin' => APPLICATION_BASE.'/modules/admin/controllers'
	))
	->setDefaultModule('admin')
	->setRouter(new Admin_Controller_Router())
	->dispatch();