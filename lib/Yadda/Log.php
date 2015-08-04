<?php

class Yadda_Log {
	protected static $_instance = null;
	
	/**
	 * Returns a singelton logger instance.
	 * 
	 * @return Zend_Log
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			$config = Zend_Registry::get('config');
			self::$_instance = Zend_Log::factory(array(
				array(
					'writerName' => 'Stream',
					'writerParams' => array(
						'stream' => 'php://output'
					),
					'filterName' => 'Priority',
					'filterParams' => array(
						'priority' => constant('Zend_Log::'.$config->log->level)
					)
				)
			));
		}
		return self::$_instance;
	}
}