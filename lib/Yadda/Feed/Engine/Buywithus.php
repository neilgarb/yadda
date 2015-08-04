<?php

class Yadda_Feed_Engine_Buywithus extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {

		// get html
		$html = $this->_fetch($feed->url);

		// parse
		$stubs = array();

		$matches = array();
		preg_match_all('#<div class="box">.*<div class="apollo_title">.*<a href="([^"]+)".*>([^<]+)</a>.*Saving you.*R([\d,]+) </div>.*<b>R([\d,]+) </b>.*<b>(\d+)<b>%.*<div class="avatar">.*<img src="([^"]+)"#sU', $html, $matches);

		for ($i = 0; $i < sizeof($matches[0]); $i ++) {
			$stub = new Yadda_Feed_Stub();
			$stub->setLink('http://www.bwu.co.za'.$this->_sanitize($matches[1][$i]));
			$stub->setGuid('http://www.bwu.co.za'.$this->_sanitize($matches[1][$i]));
			$stub->setTitle($this->_sanitize($matches[2][$i]));

			$savings = (float) str_replace(',', '.', $matches[3][$i]);
			$value = (float) str_replace(',', '.', $matches[4][$i]);

			$stub->setValue($value);
			$stub->setPrice($value - $savings);
			$stub->setDiscount((float) str_replace(',', '.', $matches[5][$i]));

			$stub->addImage('http://www.bwu.co.za'.$this->_sanitize($matches[6][$i]));
			$stubs[] = $stub;
		}

		return $stubs;
	}
}