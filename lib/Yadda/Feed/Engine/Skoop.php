<?php

class Yadda_Feed_Engine_Skoop extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		$stub = new Yadda_Feed_Stub();
		
		// fetch the listing page
		$html = $this->_fetch($feed->url);

		// link + guid
		$link = $this->_match('#<div class="main_price">.*</div>.*<a href="(.*)">.*<div id="buy_now_btn"#sU', $html);
		if ($link !== null) {
			$stub->setGuid('http://www.skoop.co.za'.$this->_sanitize($link));
			$stub->setLink('http://www.skoop.co.za'.$this->_sanitize($link));
		}
		
		// title
		$title = $this->_match('#<h1>(.*)</h1>#sU', $html);
		if ($title !== null) {
			$stub->setTitle($this->_sanitize($title));
		}
		
		// description
		$description = $this->_match('#<div id="long_desc" class="deal_description".*>(.*)</div>#sU', $html);
		if ($description !== null) {
			$stub->setDescription($this->_sanitize($description));
		}
		
		// price
		$price = $this->_match('#<div class="main_price">.*R (.*)\s*</div>#sU', $html);
		if ($price !== null) {
			$stub->setPrice((float) str_replace(',', '', $price));
		}
		
		// value
		$value = $this->_match('#value <br />.*<span class="larger">R ([\d\,\.]+)</span>#sU', $html);
		if ($value !== null) {
			$stub->setValue((float) str_replace(',', '', $value));
		}
		
		// discount
		$discount = $this->_match('#discount <br />.*<span class="larger">(\d+)%</span>#sU', $html);
		if ($discount !== null) {
			$stub->setDiscount((float) $discount);
		}
		
		// image
		$image = $this->_match('#<img src="([^"]+)"  class="main_deal" />#sU', $html);
		if ($image !== null) {
			$stub->addImage($this->_sanitize($image));
		}
		
		// geo
		$geo = $this->_match('#google.maps.LatLng\((.*)\)#sU', $html);
		if ($geo !== null) {
		    $geo = str_replace("'", '', $geo);
		    $stub->setGeo(array_map('floatval', explode(', ', $geo)));
		}
		
		return array($stub);
	}
}