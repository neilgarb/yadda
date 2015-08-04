<?php

class Yadda_Feed_Engine_Justhenga extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		$stubs = array();
		
		// fetch
		$html = $this->_fetch($feed->url);
		
		// parse
		$matches = array();
		preg_match_all('#<div class="side1">.*<img src="(/img/medium_big_thumb[^"]+)".*<h3>Description</h3>(.*)</div>.*</div>.*<div class="side2">.*([0-9]+)" title="Buy".*<p class="price"><span>R<span class="c cr"[^>]+>([0-9\.]+)</span></span></p>.*</div>#sU', $html, $matches);
		for ($i = 0; $i < sizeof($matches[0]); $i ++) {
			$stub = new Yadda_Feed_Stub();
			
			// link
			$stub->setLink($feed->url);
			
			// guid
			$id = (int) $matches[3][$i];
			$stub->setGuid('justhenga/'.$id);
			
			// description
			$stub->setDescription($this->_sanitize($matches[2][$i]));
			
			
			// price
			$stub->setPrice((float) $matches[4][$i]);
			
			// title
			$stub->setTitle(substr($stub->getDescription(), 0, 50).'... (R'.sprintf('%.2f', $stub->getPrice()).')');
			
			// image
			$stub->addImage('http://www.justhenga.com'.$this->_sanitize($matches[1][$i]));
			
			$stubs[] = $stub;
		}
		
		return $stubs;
	}
}