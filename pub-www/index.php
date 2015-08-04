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
		'www' => APPLICATION_BASE.'/modules/www/controllers'
	))
	->setDefaultModule('www')
	->setRouter(new Www_Controller_Router())
	->dispatch();