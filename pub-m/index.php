<?php

include_once '../bootstrap.php';

Zend_Layout::startMvc();

$cache = Yadda_Cache::getInstance();
if ($cache !== null) {
	Zend_Session::setSaveHandler(new Yadda_Session_SaveHandler($cache));
}

$front = Zend_Controller_Front::getInstance();
$front
	->setControllerDirectory(array(
		'm' => APPLICATION_BASE.'/modules/m/controllers'
	))
	->setDefaultModule('m')
	->setRouter(new M_Controller_Router())
	->dispatch();