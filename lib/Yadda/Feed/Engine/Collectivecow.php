<?php

class Yadda_Feed_Engine_Collectivecow extends Yadda_Feed_Engine_Abstract {
	public function import($feed) {
		$stubs = array();
		
		// fetch
		$html = $this->_fetch($feed->url);
		
		// parse
		$content = $this->_match('/<div id="content" class="grid_12">(.*)<\/div><!-- end #content-->/sU', $html);
		if ($content !== null) {
			$matches = array();
			preg_match_all('#<div class="info-box deal-listing">.*<img src="([^"]+)" class="floatleft".*<h3><a href="([^"]+)".*>([^<]+)<.*Price: R([0-9]+) \|.*((<p>.*</p>)+).*</div>#sU', $content, $matches);
			for ($i = 0; $i < sizeof($matches[0]); $i ++) {
				$stub = new Yadda_Feed_Stub();
				
				// link
				$stub->setLink('http://www.collectivecow.com'.$this->_sanitize($matches[2][$i]));
				
				// guid
				$stub->setGuid('http://www.collectivecow.com'.$this->_sanitize($matches[2][$i]));
				
				// title
				$stub->setTitle($this->_sanitize($matches[3][$i]));
				
				// description
				$stub->setDescription($this->_sanitize($matches[5][$i]));
				
				// price
				$stub->setPrice((float) $matches[4][$i]);
				
				// image
				$stub->addImage('http://www.collectivecow.com'.$this->_sanitize($matches[1][$i]));
				
				$stubs[] = $stub;
			}
		}
		return $stubs;
	}
}