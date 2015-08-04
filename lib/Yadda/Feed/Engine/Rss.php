<?php

class Yadda_Feed_Engine_Rss extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		
		// download XML
		try {
			$xml = $this->_fetch($feed->url);
			$rss = Zend_Feed::importString($xml);
		} catch (Exception $e) {
			throw new Yadda_Feed_Exception('Error getting feed: '.(string) $e);
		}
		
		$stubs = array();
		
		// iterate over each entry
		foreach ($rss as $entry) {
			
			$stub = new Yadda_Feed_Stub();
			
			$guid = $entry->guid();
			if (empty($guid)) {
				$guid = $entry->link();
			}
			$stub->setGuid($guid);
			
			$title = trim(html_entity_decode($entry->title(), ENT_QUOTES, 'utf-8'));
			$stub->setTitle($title);
			
			$stub->setLink($entry->link());
			
			$description = trim(html_entity_decode(strip_tags($entry->description()), ENT_QUOTES, 'utf-8'));
			if (!empty($description)) {
				$stub->setDescription($description);
			}
			
			$date = $entry->pubDate();
			if (!empty($date)) {
				$stub->setDate(date('Y-m-d H:i:s', strtotime($date)));
			}
			
			$price = $this->_getPrice($title.' '.(string) $description);
			if ($price !== null) {
				$stub->setPrice($price);
			}
			
			// images
			$description = $entry->description();
			if (!empty($description)) {
				$matches = array();
				preg_match_all('#src="(https?://[^"]+)"#', $description, $matches);
				if (isset($matches[1]) && sizeof($matches[1]) > 0) {
					$stub->addImage($matches[1][0]);
				}
			}
			
			$stubs[] = $stub;
		}
		
		return $stubs;
	}
	
	/**
	 * Attempts to extract a price from a title or description.
	 * 
	 * @param string $string
	 * @return float|null A float if a price is found, null otherwise.
	 */
	protected function _getPrice($string) {
		$rtn = null;
		$matches = array();
		preg_match_all('#R(\d+(\.\d\d)?)#', $string, $matches);
		if (sizeof($matches[1]) > 0) {
			foreach ($matches[1] as $match) {
				$val = (float) $match;
				if ($rtn === null || $val < $rtn) {
					$rtn = $val;
				}
			}
		}
		return $rtn;
	}
}