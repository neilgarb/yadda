<?php

class Yadda_Feed_Engine_Dealify extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		// download
		$html = $this->_fetch($feed->url);

		// parse
		$stub = new Yadda_Feed_Stub();
		
		// guid + link
		$link = $this->_match('#<h1>\s*<a href="/([^"]+)".*>\s*</h1>#sU', $html);
		if ($link !== null) {
			$stub->setGuid('http://www.dealify.com/'.$this->_sanitize($link));
			$stub->setLink('http://www.dealify.com/'.$this->_sanitize($link));
		}
		
		// title
		$title = $this->_match('#<title>(.*)</title>#sU', $html);
		if ($title !== null) {
			$stub->setTitle($this->_sanitize($title));
		}
		
		// description
		$description = $this->_match('#<div id="deal_description">(.*)</div>#sU', $html);
		if ($description !== null) {
			$stub->setDescription($this->_sanitize($description));
		}
		
		// price
		$price = $this->_match('#<span>from.*</span>.*<strong>R(\d+)</strong>#sU', $html);
		if ($price !== null) {
			$stub->setPrice((float) $price);
		}
		
		// value
		$value = $this->_match('#<span>value.*</span>.*<strong class="grey">R(\d+)</strong>#sU', $html);
		if ($value !== null) {
			$stub->setValue((float) $value);
		}
		
		// discount
		$discount = $this->_match('#<span>save.*</span>.*<strong class="grey">(\d+)%</strong>#sU', $html);
		if ($discount !== null) {
			$stub->setDiscount((float) $discount);
		}
		
		// geo
		$lat = $this->_match('#>Lat: ([\-\d\.]+)<#sU', $html);
		$long = $this->_match('#>Long: ([\-\d\.]+)<#sU', $html);
		if ($lat !== null && $long !== null) {
			$stub->setGeo(array((float) $lat, (float) $long));
		}
		
		// image
		$image = $this->_match('#<div class="frame" id="main_picture_frame">.*<img.*<img.*src="([^"]+)".*</div>#sU', $html);
		if ($image !== null) {
			$stub->addImage('http://www.dealify.com'.$this->_sanitize($image));
		}

		return array($stub);
	}
}