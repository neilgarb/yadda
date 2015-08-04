<?php

class Www_View_Helper_MinHeadLink extends Zend_View_Helper_Abstract {
	public function minHeadLink() {
		
		if (APPLICATION_ENVIRONMENT != 'production') {
			return $this->view->headLink();
		}
		
		ob_start();
		
		$urls = array();
		foreach ($this->view->headLink() as $item) {
			if ($item->type == 'text/css' && preg_match('#^/#', $item->href)) {
				$urls[] = $item->href;
			} else {
				echo $this->view->headLink()->itemToString($item);
			}
		}
		
		if (sizeof($urls) > 0) {
			
			// determine what the combined css file should be called
			$config = Zend_Registry::get('config');
			$hash = md5($config->assetVersion.serialize($urls));
			$file = '/css/cache/'.$hash.'.css';
			$filePath = APPLICATION_BASE.'/pub-www'.$file;
			$fileDir = dirname($filePath);
			if (!is_dir($fileDir)) {
				
				// create the cache folder
				@mkdir($fileDir, 0755, true);
			}
			if (!file_exists($filePath)) {
				
				// write the combined css to disk
				$css = '';
				foreach ($urls as $url) {
					$css .= @file_get_contents(APPLICATION_BASE.'/pub-www'.$url)."\n";
				}
				@file_put_contents($filePath, $css);
			}
			if (file_exists($filePath)) {
				
				// minify
				$fileMin = '/css/cache/'.$hash.'.min.css';
				$filePathMin = APPLICATION_BASE.'/pub-www'.$fileMin;
				
				if (!file_exists($filePathMin)) {
					
					// try to minify
					$jar = APPLICATION_BASE.'/bin/yuicompressor-2.4.6.jar';
					@system('java -jar "'.$jar.'" -o "'.$filePathMin.'" '.$filePath);
				}
				
				if (file_exists($filePathMin)) {
					echo $this->view->headLink()->itemToString((object) array(
						'type' => 'text/css',
						'rel' => 'stylesheet',
						'media' => 'screen',
						'href' => $fileMin
					));
				} else {
					echo $this->view->headLink()->itemToString((object) array(
						'type' => 'text/css',
						'rel' => 'stylesheet',
						'media' => 'screen',
						'href' => $file
					));
				}
			} else {
				
				// couldn't write combined css, so serve individual files
				foreach ($urls as $url) {
					echo $this->view->headLink()->itemToString((object) array(
						'type' => 'text/css',
						'rel' => 'stylesheet',
						'media' => 'screen',
						'href' => $url.'?v='.$config->assetVersion
					));
				}
			}
		}
		
		return ob_get_clean();
	}
}