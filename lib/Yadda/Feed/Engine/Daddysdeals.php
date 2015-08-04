<?php

class Yadda_Feed_Engine_Daddysdeals extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		$stubs = array();
		
		// select city
		$retries = 3;
		for ($i = 0; $i < $retries; $i ++) {
			try {
				$config = Zend_Registry::get('config');
				$client = new Zend_Http_Client($feed->url, array(
					'useragent' => $config->userAgent,
					'timeout' => 20
				));
				$client->setCookieJar(true);
				$client->request();
				
				// success!
				break;
			} catch (Zend_Http_Exception $e) {
				
				// if last retry fails, throw error
				if ($i == $retries - 1) {
					throw new Yadda_Feed_Exception('Error selecting city.');
				}
			}
			
			// retry
			sleep(1);
		}
		
		// fetch home page
		try {
			$client->setUri('https://www.daddysdeals.co.za/');
			$response = $client->request();
		} catch (Zend_Http_Exception $e) {
			throw new Yadda_Feed_Exception('Error downloading feed.');
		}
		
		$html = $response->getBody();
		
		$matches = array();
		@preg_match_all('#<div class="deal-content">.*<h1><a href="([^"]+)"[^>]*>(.*)</a>.*<div class="deal-gallery">.*<img src="([^"]+)".*<div class="deal-info-buy">.*<span>R([0-9\.]+)</span>.*</div>.*<div class="deal-content-highlights">.*<h3>At a glance</h3>(.*)</div>.*</div>#sU', $html, $matches);
		
		for ($i = 0; $i < sizeof($matches[0]); $i ++) {
			$stub = new Yadda_Feed_Stub();
			$stub->setLink($this->_sanitize($matches[1][$i]));
			$stub->setGuid($this->_sanitize($matches[1][$i]));
			$stub->setTitle($this->_sanitize($matches[2][$i]));
			$stub->addImage($this->_sanitize($matches[3][$i]));
			$stub->setPrice((float) $matches[4][$i]);
			$stub->setDescription($this->_sanitize($matches[5][$i]));
			$stubs[] = $stub;
		}
		
		return $stubs;
	}
}