<?php

class Www_View_Helper_MinHeadScript extends Zend_View_Helper_Abstract {
	public function minHeadScript() {
		if (APPLICATION_ENVIRONMENT != 'production') {
			return $this->view->headScript();
		}
		
		ob_start();
		
		$urls = array();
		$sources = '';
		foreach ($this->view->headScript() as $item) {
			if (isset($item->attributes['src']) && preg_match('#^/#', $item->attributes['src'])) {
				$urls[] = $item->attributes['src'];
			} elseif (!empty($item->source)) {
				$sources .= $this->view->headScript()->itemToString($item, null, '//<![CDATA[', '//]]>');
			} else {
				echo $this->view->headScript()->itemToString($item, null, '', '');
			}
		}
		
		if (sizeof($urls) > 0) {
			// determine what the combined js file should be called
			$config = Zend_Registry::get('config');
			$hash = md5($config->assetVersion.serialize($urls));
			$file = '/js/cache/'.$hash.'.js';
			$filePath = APPLICATION_BASE.'/pub-www'.$file;
			$fileDir = dirname($filePath);
			if (!is_dir($fileDir)) {
				
				// create the cache folder
				@mkdir($fileDir, 0755, true);
			}
			if (!file_exists($filePath)) {
				
				// write the combined js to disk
				$js = '';
				foreach ($urls as $url) {
					$js .= @file_get_contents(APPLICATION_BASE.'/pub-www'.$url)."\n";
				}
				@file_put_contents($filePath, $js);
			}
			if (file_exists($filePath)) {
				
				// minify
				$fileMin = '/js/cache/'.$hash.'.min.js';
				$filePathMin = APPLICATION_BASE.'/pub-www'.$fileMin;
				
				if (!file_exists($filePathMin)) {
					
					// try to minify
					$jar = APPLICATION_BASE.'/bin/yuicompressor-2.4.6.jar';
					@system('java -jar "'.$jar.'" -o "'.$filePathMin.'" '.$filePath);
				}
				
				if (file_exists($filePathMin)) {
					echo $this->view->headScript()->itemToString((object) array(
						'type' => 'text/javascript',
						'attributes' => array('src' => $fileMin)
					), null, '', '');
				} else {
					echo $this->view->headScript()->itemToString((object) array(
						'type' => 'text/javascript',
						'attributes' => array('src' => $file)
					), null, '', '');
				}
			} else {
				
				// couldn't write combined js, so serve individual files
				foreach ($urls as $url) {
					echo $this->view->headScript()->itemToString((object) array(
						'type' => 'text/javascript',
						'attributes' => array('src' => $url.'?v='.$config->assetVersion)
					), null, '', '');
				}
			}
		}
		
		echo $sources;
		
		return ob_get_clean();
	}
}