<?php

class Yadda_Db {
	protected static $_instance = null;
	
	/**
	 * Lazy loads the database connection.
	 * 
	 * @return Zend_Db_Adapter_Abstract
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			$config = Zend_Registry::get('config');
			self::$_instance = Zend_Db::factory(
				$config->db->driver,
				$config->db->config->toArray()
			);
			self::$_instance->query("SET NAMES 'UTF8'");
		}
		return self::$_instance;
	}
	
	/**
	 * Returns an object representing 'now' when doing a database insert or
	 * update.
	 * 
	 * @return Zend_Db_Expr
	 */
	public static function now() {
		return new Zend_Db_Expr('NOW()');
	}
}