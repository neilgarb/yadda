<?php

// application environment
$env = 'production';
if (function_exists('apache_getenv') && ($test = apache_getenv('APPLICATION_ENVIRONMENT')) !== false) {
	$env = $test;
}
if (defined('STDIN') && ($test = getopt('e:')) !== false) {
	if (isset($test['e'])) {
		$env = $test['e'];
	}
}
define('APPLICATION_ENVIRONMENT', $env);

// errors
if (APPLICATION_ENVIRONMENT == 'development' || defined('STDIN')) {
	ini_set('display_errors', '1');
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', '0');
	error_reporting(E_ERROR);
}

// defaults
date_default_timezone_set('Africa/Johannesburg');
define('APPLICATION_BASE', dirname(__FILE__));
set_include_path(APPLICATION_BASE.'/lib');

// autoloader
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Yadda_');
$loader->registerNamespace('Www_');
$loader->registerNamespace('M_');
$loader->registerNamespace('Data_');
$loader->registerNamespace('Admin_');

// config
$config = new Zend_Config_Ini(APPLICATION_BASE.'/config.ini', APPLICATION_ENVIRONMENT);
Zend_Registry::set('config', $config);

if (($cache = Yadda_Cache::getInstance()) !== null) {
	Zend_Db_Table::setDefaultMetadataCache($cache);
}