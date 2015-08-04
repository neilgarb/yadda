<?php

class Yadda_Feed_Engine_Zappon extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		$return = array();
		
		// get the HTML
		$html = $this->_fetch($feed->url);
		
		// parse the HTML
		$liMatches = array();
		@preg_match_all('#<li>(.*)</li>#sU', $html, $liMatches);
		foreach ($liMatches[1] as $liMatch) {
			
			$stub = new Yadda_Feed_Stub();
			
			// title + link
			$pMatches = array();
			@preg_match_all('#<p class="clsRecentDealHd">.*<a href="([^"]+)".*>([^<]+)</a></p>#sU', $liMatch, $pMatches);
			if (sizeof($pMatches[0]) == 0) {
				continue;
			}
			$link = 'http://www.zappon.co.za'.$this->_sanitize($pMatches[1][0]);
			$stub->setGuid($link);
			$stub->setLink($link);
			
			$title = $this->_sanitize($pMatches[2][0]);
			$stub->setTitle($title);
			
			// description
			$description = $this->_getDescription($link);
			if (!empty($description)) {
				$stub->setDescription($description);
			}
			
			// price
			$price = $this->_match('#<p class="bought-count">R<span class="c cr" title=".*>(\d+)</span></p>#sU', $liMatch);
			if ($price !== null) {
				$stub->setPrice((float) $price);
			}
			
			// value
			$value = $this->_match('#<span>Value : R<span class="c cr" title="[^"]+">(\d+)</span>#sU', $liMatch);
			if ($value !== null) {
				$stub->setValue((float) $value);
			}
			
			// discount
			$discount = $this->_match('#<span>Discount : <span class="c1" title="[^"]+">(\d+)</span>%</span>#sU', $liMatch);
			if ($discount !== null) {
				$stub->setDiscount((float) $discount);
			}
			
			// image
			$image = $this->_match('#<img.*src="([^"]+)"#sU', $liMatch);
			if ($image !== null) {
				$stub->addImage('http://www.zappon.co.za'.$this->_sanitize($image));
			}
			
			$return[] = $stub;
		}
		
		return $return;
	}
	
	protected function _getDescription($url) {
		try {
			$body = $this->_fetch($url);
		} catch (Exception $e) {
			return null;
		}
		
		$description = $this->_match('#Description</h3>(.*)</div>#sU', $body);
		if ($description === null) {
			return null;
		}
		return $this->_sanitize($description);
	}
}