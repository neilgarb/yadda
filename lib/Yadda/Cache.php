<?php

class Yadda_Cache {
	protected static $_instance = null;
	
	/**
	 * Returns a singleton instance of Zend_Cache, or null if the cache is not
	 * available.
	 * 
	 * @return Zend_Cache|null
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			try {
				$config = Zend_Registry::get('config');
				self::$_instance = Zend_Cache::factory(
					'Core', 'Memcached',
					array(
						'lifetime' => 300,
						'automatic_serialization' => true
					),
					array(
						'servers' => array(array(
							'host' => $config->cache->host,
							'port' => $config->cache->port
						))
					)
				);
			} catch (Zend_Cache_Exception $e) {
				
			}
		}
		return self::$_instance;
	}
}