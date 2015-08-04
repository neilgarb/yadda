<?php

class Yadda_Feed_Engine_Citymob extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		// fetch HTML
		$xml = $this->_fetch($feed->url);
		
		// parse
		$stubs = array();
		$doc = @simplexml_load_string($xml);
		if ($doc === false) {
			throw new Yadda_Feed_Exception('Error parsing Citymob XML feed.');
		}
		foreach ($doc->deals->deal as $deal) {
			$stub = new Yadda_Feed_Stub();
			$stub->setGuid($this->_sanitize($deal->deal_url));
			$stub->setLink($this->_sanitize($deal->deal_url));
			$stub->setTitle($this->_sanitize($deal->short_title));
			$stub->setDescription($this->_sanitize($deal->title));
			$stub->setPrice((float) str_replace('ZAR', '', $deal->price));
			$stub->setValue((float) str_replace('ZAR', '', $deal->value));
			$stub->setDiscount((float) str_replace('ZAR', '', $deal->discount_percent));
			try {
				$stub->setGeo(array(
					(float) $deal->vendor_latitude,
					(float) $deal->vendor_longitude
				));
			} catch (Yadda_Feed_Exception $e) {
				// ignore
			}
			$stub->addImage($this->_sanitize($deal->large_image_url));
			$stubs[] = $stub;
		}
		return $stubs;
	}
}