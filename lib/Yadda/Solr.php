<?php

class Yadda_Solr {
	protected static $_instance = null;
	
	/**
	 * Returns a singleton instance of Yadda_Solr.
	 * 
	 * @return Yadda_Solr
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function __construct() {
		// cannot be instantiated
	}
	
	/**
	 * Performs a GET request on Solr.
	 * 
	 * @param string $path Relative path from $this->_getBaseUrl()
	 * @param array $params
	 * @return string The response body
	 * @throws Yadda_Solr_Exception
	 */
	public function get($path, $params) {
		$client = new Zend_Http_Client();
		$queryString = '?';
		$queryParams = array();
		foreach ($params as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $valueItem) {
					$queryParams[] = rawurlencode($key).'='.rawurlencode($valueItem);
				}
			} else {
				$queryParams[] = rawurlencode($key).'='.rawurlencode($value);
			}
		}
		$client->setUri($this->_getBaseUrl().$path.'?'.join('&', $queryParams));
		try {
			$response = $client->request();
		} catch (Zend_Http_Exception $e) {
			throw new Yadda_Solr_Exception('Error querying Solr.');
		}
		return $response->getBody();
	}
	
	/**
	 * Returns Solr's base URL.
	 * 
	 * @return string
	 */
	protected function _getBaseUrl() {
		return 'http://localhost:8983/solr';
	}
}
