<?php

class Yadda_Store {
	protected static $_instance = null;
	
	/**
	 * Returns a singleton instance of Yadda_Store.
	 * 
	 * @return Yadda_Store
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	protected function __construct() {
		// cannot be instantiated directly
	}
	
	/**
	 * Uploads a file to the asset store.
	 * 
	 * @param string $filename
	 * @param string $key
	 * @return Yadda_Store
	 */
	public function put($filename, $key) {
		$key = self::_normalize($key);
		$basePath = self::_getStorePath();
		
		// make sure the destination path exists
		$path = $basePath.dirname($key);
		if (!is_dir($path)) {
			@mkdir($path, 0755, true);
		}
		if (!is_dir($path)) {
			throw new Yadda_Store_Exception('Error creating destination path');
		}
		
		// copy the file to its destination
		@copy($filename, $basePath.$key);
		if (!file_exists($basePath.$key)) {
			throw new Yadda_Store_Exception('Error copying file to new location');
		}
		
		return $this;
	}
	
	/**
	 * Downloads a file from the store into $filename.
	 * 
	 * @param string $key
	 * @param string $filename
	 * @return Yadda_Store
	 */
	public function get($key, $filename) {
		$key = self::_normalize($key);
		$basePath = self::_getStorePath();
		
		// does the key exist?
		$keyFilename = $basePath.$key;
		if (!file_exists($keyFilename)) {
			throw new Yadda_Store_Exception('There is no such key in the store');
		}
		
		// copy the key to filename
		@copy($keyFilename, $filename);
		if (!file_exists($filename)) {
			throw new Yadda_Store_Exception('Error downloading asset from store');
		}
		
		return $this;
	}
	
	/**
	 * Returns a list of keys which match the provided prefix.
	 * 
	 * @param string $prefix
	 * @param array
	 * @return void
	 */
	public function search($prefix) {
		$prefix = self::_normalize($prefix);
		$path = self::_getStorePath();
	}
	
	/**
	 * Normalizes a store key.
	 * 
	 * @param string $key
	 * @return string
	 */
	protected function _normalize($key) {
		$key = '/'.ltrim($key, '/');
		return $key;
	}
	
	/**
	 * Returns the path of the asset store.
	 * 
	 * @throws Yadda_Image_Exception
	 * @return string
	 */
	protected function _getStorePath() {
		$config = Zend_Registry::get('config');
		$path = $config->store->path;
		if (!is_dir($path)) {
			@mkdir($path, 0755, true);
		}
		if (!is_dir($path)) {
			throw new Yadda_Image_Exception('Error creating store path');
		}
		return rtrim($path, '/');
	}
}