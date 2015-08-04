<?php

class Yadda_Feed_Engine_247deals extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		$stub = new Yadda_Feed_Stub();
		
		// fetch
		$html = $this->_fetch($feed->url);
		
		// link
		$stub->setLink($feed->url);
		
		// guid
		$guid = $this->_match('#did=([^\']+)\'#s', $html);
		if ($guid !== null) {
			$stub->setGuid('247deals/'.$guid);
		}
		
		// title
		$title = $this->_match('#<h1 id="deal_title"><a href=.*>(.*)</a></h1>#sU', $html);
		if ($title !== null) {
			$stub->setTitle($this->_sanitize($title));
		}
		
		// description
		$description = $this->_match('#<div class="tab_second_content">(.*)</div>#sU', $html);
		if ($description !== null) {
			$stub->setDescription($this->_sanitize($description));
		}
		
		// price
		$price = $this->_match('#<div id="buynow_price">R([^<]+)</div>#sU', $html);
		if ($price !== null) {
			$stub->setPrice((float) $price);
		}
		
		// value
		$value = $this->_match('#Value<br />.*<span>.*R(\d+)</span>#sU', $html);
		if ($value !== null) {
			$stub->setValue((float) $value);
		}
		
		// discount
		$discount = $this->_match('#Discount<br />.*<span>.*(\d+)%</span>#sU', $html);
		if ($discount !== null) {
			$stub->setDiscount((float) $discount);
		}
		
		// image
		$image = $this->_match('#<div class="panel" title="1".*src="([^"]+)"#sU', $html);
		if ($image !== null) {
			$stub->addImage('http://247deals.co.za/'.$image);
		}
		
		return array($stub);
	}
}