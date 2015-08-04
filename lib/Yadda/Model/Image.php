<?php

class Yadda_Model_Image {
	
	/**
	 * Returns all images matching the given parameters.
	 * 
	 * @param array $params
	 * @return array
	 */
	public static function all(array $params) {
		$imageDb = Yadda_Db_Table::getInstance('image');
		$select = $imageDb
			->select()
			->from('image')
			->order('id DESC');
			
		// check parameters
		
		if (isset($params['deal'])) {
			$select->where('deal_id = ?', $params['deal']);
		}
		
		// fetch
		
		$images = $imageDb->fetchAll($select);
		
		$return = array();
		foreach ($images as $image) {
			$return[] = self::toArray($image);
		}
		
		return $return;
	}
	
	/**
	 * Fetches an image from its source URL, and puts it into the asset store.
	 * 
	 * @param Zend_Db_Table_Row $image
	 * @throws Yadda_Model_Exception
	 * @return void
	 */
	public static function fetch($image) {
		// create temporary directory
		$tmp = uniqid('/tmp/');
		@mkdir($tmp);
		if (!is_dir($tmp)) {
			throw new Yadda_Model_Exception('Error creating temp directory');
		}

		// tidy up the URL
		$uri = str_replace(' ', '%20', $image->source);
		
		// download image into temporary directory
		$config = Zend_Registry::get('config');
		$client = new Zend_Http_Client($uri, array(
			'timeout' => 60,
			'useragent' => $config->userAgent,
			'output_stream' => $tmp.'/original'
		));
		$client->setHeaders('Accept-encoding', 'identity');
		try {
			$client->request();
		} catch (Zend_Exception $e) {
			@system('rm -rf '.$tmp);
			throw new Yadda_Model_Exception('Error downloading image');
		}
		
		// convert image
		try {
			Yadda_Image::convert($tmp.'/original', $tmp.'/converted.jpg', Yadda_Image::JPEG);
		} catch (Yadda_Image_Exception $e) {
			@system('rm -rf '.$tmp);
			throw new Yadda_Model_Exception('Error converting image to JPEG');
		}
		
		// upload converted image to asset store
		try {
			Yadda_Store::getInstance()->put(
				$tmp.'/converted.jpg',
				'/deal/'.$image->deal_id.'/image/'.$image->id.'/original.jpg'
			);
		} catch (Yadda_Store_Exception $e) {
			@system('rm -rf '.$tmp);
			throw new Yadda_Model_Exception('Error uploading image to store');
		}
		
		// done!
		@system('rm -rf '.$tmp);
	}
	
	/**
	 * Converts an image object to an array.
	 * 
	 * @param Zend_Db_Table_Row $image
	 * @return array
	 */
	public static function toArray($image) {
		return array(
			'id' => (int) $image->id
		);
	}
}