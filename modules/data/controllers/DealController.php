<?php

class DealController extends Data_Controller_Action {
	public function thumbAction() {
		$id = (int) $this->_getParam('id');
		$images = Yadda_Model_Image::all(array('deal' => $id));
		$imageId = $images[0]['id'];
		
		// create a temporary directory
		$tmp = uniqid('/tmp/');
		@mkdir($tmp);
		if (!is_dir($tmp)) {
			throw new Data_Exception_Error('Error creating tmp dir');
		}
		
		// fetch the original
		try {
			Yadda_Store::getInstance()->get(
				'/deal/'.$id.'/image/'.$imageId.'/original.jpg',
				$tmp.'/original.jpg'
			);
		} catch (Yadda_Store_Exception $e) {
			// use default
			@copy(APPLICATION_BASE.'/pub-data/img/'.$this->_getParam('filename'), $tmp.'/original.jpg');
		}
		
		// resize
		try {
			Yadda_Image::resize(
				$tmp.'/original.jpg',
				$tmp.'/'.$this->_getParam('filename'),
				array(
					'width' => $this->_getParam('width'),
					'height' => $this->_getParam('height'),
					'crop' => $this->_getParam('crop')
				),
				Yadda_Image::JPEG
			);
		} catch (Yadda_Image_Exception $e) {
			@system('rm -rf '.$tmp);
			throw new Data_Exception_Error('Error resizing image');
		}
		
		// prepare to serve the jpg file
		$this->getResponse()->setHeader('Content-Type', 'image/jpeg');
		$this->getResponse()->setBody(@file_get_contents($tmp.'/'.$this->_getParam('filename')));
		$this->getResponse()->setHeader('Cache-Control', 'public, max-age='.(7 * 24 * 60 * 60));
		
		// cleanup
		@system('rm -rf '.$tmp);
		
		$this->getResponse()->sendResponse();
		die;
	}
}