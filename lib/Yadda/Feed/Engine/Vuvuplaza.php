<?php

class Yadda_Feed_Engine_Vuvuplaza extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		// fetch
		$html = $this->_fetch($feed->url);
		
		// parse
		$matches = array();
		@preg_match_all(
		    '#<div class="main-deal">.*<h2><a.*href="([^"]+)".*>([^<]*)</a>'.
		    '</h2>.*<span class="price-main">R([\d\.]+)</span>.*<span class="price-savings">'.
		    'save (\d+)%</span>.*Value<br /><b>R(\d+)</b>.*<div class="main-deal-right">.*'.
		    '<img src="([^"]+)".*<div class="more-content-left">(.*)<div class="more-content-right">#sU',
		    $html, $matches);

		if (sizeof($matches[0]) == 0) {
			return array();
		}
		
		$stub = new Yadda_Feed_Stub();
		$stub->setGuid('http://www.vuzuplaza.com'.$this->_sanitize($matches[1][0]));
		$stub->setLink('http://www.vuzuplaza.com'.$this->_sanitize($matches[1][0]));
		$stub->setTitle($this->_sanitize($matches[2][0]));
		$stub->setDescription($this->_sanitize($matches[7][0]));
		$stub->setPrice((float) $matches[3][0]);
		$stub->setDiscount((float) $matches[4][0]);
		$stub->setValue((float) $matches[5][0]);
		$stub->addImage('http://www.vuvuplaza.com'.$this->_sanitize($matches[6][0]));

		return array($stub);
	}
}