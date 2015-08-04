<?php

class Yadda_Db_Table {
	protected static $_instances = array();
	
	/**
	 * Returns a singleton instance of the Zend_Db_Table object for this table.
	 * 
	 * @param string $table
	 * @return Zend_Db_Table
	 */
	public static function getInstance($table) {
		if (!isset(self::$_instances[$table])) {
			self::$_instances[$table] = new Zend_Db_Table(array(
				'name' => $table,
				'db' => Yadda_Db::getInstance()
			));
		}
		return self::$_instances[$table];
	}
}