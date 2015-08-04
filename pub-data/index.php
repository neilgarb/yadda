<?php

include_once '../bootstrap.php';

Zend_Layout::startMvc();

$front = Zend_Controller_Front::getInstance();
$front
	->setControllerDirectory(array(
		'data' => APPLICATION_BASE.'/modules/data/controllers'
	))
	->setDefaultModule('data')
	->setRouter(new Data_Controller_Router())
	->dispatch();