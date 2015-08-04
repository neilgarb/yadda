<?php

class Yadda_Feed_Engine_Wicount extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		$stub = new Yadda_Feed_Stub();
		
		// fetch the page
		$html = $this->_fetch($feed->url);
		
		// link + guid
		$link = $this->_match('#<fb:like href="([^"]+)"#sU', $html);
		if ($link !== null) {
			$stub->setGuid($this->_sanitize($link));
			$stub->setLink($this->_sanitize($link));
		}
		
		// title
		$title = $this->_match('#<span id="lblName">([^<]+)</span>#sU', $html);
		if ($title !== null) {
			$stub->setTitle($this->_sanitize($title));
		}
		
		// description
		$description = $this->_match('#<a name="Deal"></a>Here\'s the Deal!(.*)<div#sU', $html);
		if ($description !== null) {
			$stub->setDescription($this->_sanitize($description));
		}
		
		// price
		$price = $this->_match('#<span id="lblSitePrice" class="SitePrice">R ([\d\.]+)</span>#sU', $html);
		if ($price !== null) {
			$stub->setPrice((float) $price);
		}
		
		// value
		$value = $this->_match('#<span id="ProductDealControl1_lblValue">R ([\d\.]+)</span>#sU', $html);
		if ($value !== null) {
			$stub->setValue((float) $value);
		}
		
		// discount
		$discount = $this->_match('#<span id="ProductDealControl1_lblDiscount">(\d+)%</span>#sU', $html);
		if ($discount !== null) {
			$stub->setDiscount((float) $discount);
		}
		
		// image
		$image = $this->_match('#<img alt="[^"]+".*src="([^"]+)" /></div>#sU', $html);
		if ($image !== null) {
			$stub->addImage($this->_sanitize($image));
		}
		
		// return
		return array($stub);
	}
}