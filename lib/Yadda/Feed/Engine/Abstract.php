<?php

abstract class Yadda_Feed_Engine_Abstract implements Yadda_Feed_Engine_Interface {
	
	/**
	 * Fetches the contents of a URL.
	 * 
	 * @param string $url
	 * @param bool $useCookieJar
	 * @param bool $ignoreHttpError
	 * @return string
	 * @throws Yadda_Feed_Exception
	 */
	protected function _fetch($url, $useCookieJar = false, $ignoreHttpError = false) {
		$config = Zend_Registry::get('config');
		$retries = 3;
		
		// try 3 times
		$client = new Zend_Http_Client($url, array(
			'timeout' => 30,
			'useragent' => $config->userAgent
		));
		if ($useCookieJar === true) {
			$client->setCookieJar(true);
		}
		for ($i = 0; $i < $retries; $i ++) {
			try {
				$response = $client->request();
				if ((bool) $ignoreHttpError === false) {
					if ($response->getStatus() != 200) {
						throw new Zend_Http_Exception('Didn\'t get 200 OK.');
					}
				}
				
				// request was successful, so break out of retry loop
				break;
			} catch (Zend_Exception $e) {
				
				// if we're on the last retry, throw an exception
				if ($i == $retries - 1) {
					throw new Yadda_Feed_Exception('Error fetching URL: '.(string) $e);
				}
			}
			
			// retry!
			sleep(1);
		}
		return $response->getBody();
	}
	
	/**
	 * Returns the first match of regex $pattern in $string, or null if no
	 * match.
	 * 
	 * @param string $pattern
	 * @param string $string
	 * @param int $index The index of the matches array, e.g. 2 will check
	 *                   $matches[2].
	 * @return string|null
	 */
	protected function _match($pattern, $string, $index = 1) {
		$matches = array();
		preg_match_all($pattern, $string, $matches);
		if (sizeof($matches[$index]) > 0) {
			return $matches[$index][0];
		}
		return null;
	}
	
	/**
	 * Strips a string of HTML tags and entities.
	 * 
	 * @param string $string
	 * @return string
	 */
	protected function _sanitize($string) {
		return trim(html_entity_decode(strip_tags((string) $string), ENT_QUOTES, 'utf-8'));
	}
}