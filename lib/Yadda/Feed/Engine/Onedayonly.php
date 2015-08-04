<?php

class Yadda_Feed_Engine_Onedayonly extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		$stub = new Yadda_Feed_Stub();
		
		// fetch
		$html = $this->_fetch($feed->url);
		
		// link
		$stub->setLink('http://www.onedayonly.co.za/');
		
		// guid
		$guid = $this->_match('#<input type="hidden" name="product_id" value="([^"]+)" />#sU', $html);
		if ($guid !== null) {
			$stub->setGuid('onedayonly/'.$guid);
		}
		
		// title
		$title = $this->_match('#<td.*class="product_name".*>([^<]+)</td>#sU', $html);
		if ($title !== null) {
			$stub->setTitle($this->_sanitize($title));
		}
		
		// description
		$description = $this->_match('#<div class="product_s_desc">(.*)</td>#sU', $html);
		if ($description !== null) {
			$stub->setDescription($this->_sanitize($description));
		}
		
		// price
		$price = $this->_match('#<span class="productPrice">([^<]+)</span>#sU', $html);
		if ($price !== null) {
			$stub->setPrice((float) str_replace('R', '', trim($price)));
		}
		
		// value
		$value = $this->_match('#Retail price: <s>R([\d\.]+)</s>#sU', $html);
		if ($value !== null) {
			$stub->setValue((float) $value);
		}
		
		// discount
		if ($stub->getValue() !== null && $stub->getPrice() !== null && $stub->getValue() > 0) {
			$stub->setDiscount(($stub->getValue() - $stub->getPrice()) / $stub->getValue() * 100); 
		}
		
		// image
		$image = $this->_match('#<a.*rel="lightbox"><img src="([^"]+)" class="browseProductImage"#sU', $html);
		if ($image !== null) {
			$stub->addImage($this->_sanitize($image));
		}
		
		return array($stub);
	}
}