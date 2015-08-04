<?php

class Yadda_Feed_Engine_24hoursonly extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		$stub = new Yadda_Feed_Stub();
		
		// fetch
		$html = $this->_fetch($feed->url);

		// link + guid
		$link = $this->_match('#<meta name="og:url" content="([^"]+)" />#sU', $html);
		if ($link !== null) {
			$stub->setLink($this->_sanitize($link));
			$stub->setGuid($this->_sanitize($link));
		}
		
		// title
		$title = $this->_match('#<h1>([^<]+)</h1>#sU', $html);
		if ($title !== null) {
			$stub->setTitle($this->_sanitize($title));
		}
		
		// description
		$description = $this->_match('#<div class="product-details".*>(.*)</div>#sU', $html);
		if ($description !== null) {
			$stub->setDescription($this->_sanitize($description));
		}
		
		// price
		$price = $this->_match('#<div class="price">([^<]+)</div>#sU', $html);
		if ($price !== null) {
			$stub->setPrice((float) $price);
		}
		
		// value
		$value = $this->_match('#<span class="rrp">R.*([\d\.]+)</span>#sU', $html);
		if ($value !== null) {
			$stub->setValue((float) $value);
		}
		
		// discount
		if ($stub->getPrice() !== null && $stub->getValue() !== null && $stub->getValue() > 0) {
			$stub->setDiscount(($stub->getValue() - $stub->getPrice()) / $stub->getValue() * 100);
		}
		
		// image
		$image = $this->_match('#<img id="MainPic".*src="([^"]+)"#sU', $html);
		if ($image !== null) {
			$stub->addImage('http://www.24hoursonly.co.za/'.$image);
		}
		
		return array($stub);
	}
}