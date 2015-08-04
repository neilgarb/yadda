<?php

class Yadda_Feed_Engine_Fooddeals extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {

		// fetch
		$html = $this->_fetch($feed->url);

		// parse
		$stub = new Yadda_Feed_Stub();

		$link = $this->_sanitize($this->_match('#<fb:comments.*href="([^"]+)".*></fb:comments>#sU', $html));
		$stub->setLink($link);
		$stub->setGuid($link);

		$stub->setTitle($this->_sanitize($this->_match('#<h1>Today\'s Deal: <span>([^<]+)</span></h1>#sU', $html)));

		$stub->setPrice((float) $this->_match('#<span class="price_value" id="price_value-1">R(\d+)  </span>#sU', $html));
		$stub->setValue((float) $this->_match('#<h3>Value</h3>.*<span>R(\d+)</span>#sU', $html));
		$stub->setDiscount((float) $this->_match('#<h3>Discount</h3>.*<span>(\d+)%</span>#sU', $html));

		$stub->addImage($this->_sanitize($this->_match('#<div class="banner_middle".*>.*<img src="([^"]+)"#sU', $html)));

		$geo = $this->_match('#&amp;ll=([\-\d\.,]+)&#sU', $html);
		if ($geo !== null) {
			$stub->setGeo(array_map('floatval', explode(',', $geo)));
		}

		return array($stub);
	}
}
