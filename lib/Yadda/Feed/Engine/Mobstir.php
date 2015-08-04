<?php

class Yadda_Feed_Engine_Mobstir extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		// download
		$html = $this->_fetch($feed->url, false, true);
		
		// parse
		$stub = new Yadda_Feed_Stub();
		
		// guid
		$id = $this->_match('#product/(\d+)/\' id="scriptbuynow"#sU', $html);
		if ($id !== null) {
			$stub->setGuid('mobstir/'.$id);
			$stub->setLink('http://www.mobstir.co.za/?id='.$id);
		}
		
		// title
		$title = $this->_match('#<meta name="description" content="([^"]+)" />#sU', $html);
		if ($title !== null) {
			$stub->setTitle($this->_sanitize($title));
		}
		
		// description
		$description = $this->_match('#<div class="des-panel">.*<h3>description</h3>(.*)</div>#sU', $html);
		if ($description !== null) {
			$stub->setDescription($this->_sanitize($description));
		}
		
		// value
		$value = $this->_match('#value.*<h3>.*R (\d+)\s+</h3>#sU', $html);
		if ($value !== null) {
			$stub->setValue((float) $value);
		}
		
		// discount
		$discount = $this->_match('#discount.*<h3>(\d+)%</h3>#sU', $html);
		if ($discount !== null) {
			$stub->setDiscount((float) $discount);
		}
		
		// price
		$price = $this->_match('#R([\d\.]+)#', $stub->getTitle());
		if ($price !== null) {
			$stub->setPrice((float) $price);
		}
		
		// image
		$image = $this->_match('#<div class="banner_middle.*background: url\(([^\)]+)\)#sU', $html);
		if ($image !== null) {
			$stub->addImage($this->_sanitize($image));
		}
		
		return array($stub);
	}
}