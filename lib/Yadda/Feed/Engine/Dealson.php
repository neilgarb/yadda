<?php

class Yadda_Feed_Engine_Dealson extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		// download feed
		$html = $this->_fetch($feed->url, true);
		
		// parse
		$stub = new Yadda_Feed_Stub();
		
		// link + guid
		$link = $this->_match('#<fb:like href="([^"]+)"#sU', $html);
		if ($link !== null) {
			$stub->setGuid($this->_sanitize($link));
			$stub->setLink($this->_sanitize($link));
		}
		
		// title
		$title = $this->_match('#<h1 class="deal-title">([^<]+)</h1>#sU', $html);
		if ($title !== null) {
			$stub->setTitle($this->_sanitize($title));
		}
		
		// description
		$description = $this->_match('#<div class="highlights">.*<h3>Highlights</h3>(.*)</div>#sU', $html);
		if ($description !== null) {
			$stub->setDescription($this->_sanitize($description));
		}
		
		// price
		$price = $this->_match('#<div class=\'price\'>R([\d\.]+)</div>#sU', $html);
		if ($price !== null) {
			$stub->setPrice((float) $price);
		}
		
		// value
		$value = $this->_match('#<tr>.*<td>R([\d\.]+)</td>#sU', $html);
		if ($value !== null) {
			$stub->setValue((float) $value);
		}
		
		// discount
		$discount = $this->_match('#<tr>.*<td>(\d+)%</td>#sU', $html);
		if ($discount !== null) {
			$stub->setDiscount((float) $discount);
		}
		
		// image
		$image = $this->_match('#<div class="worklet-content"><img.*src="([^"]+)"#sU', $html);
		if ($image !== null) {
			$stub->addImage($this->_sanitize($image));
		}
		
		return array($stub);
	}
}