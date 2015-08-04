<?php

include_once '../bootstrap.php';

set_time_limit(0);

$logger = Yadda_Log::getInstance();

// get all images that need to be fetched
$imageDb = Yadda_Db_Table::getInstance('image');
$select = $imageDb
	->select()
	->from('image')
	->where('status = ?', 'new')
	->where('source LIKE ?', 'http%')
	->where('created >= ?', date('Y-m-d H:i:s', strtotime('-1 day')));
$images = $imageDb->fetchAll($select);

foreach ($images as $image) {
	
	// mark the image as 'fetching' -- if the update doesn't return > 0 then
	// either the row has been deleted or it is already being fetched by 
	// another process
	$rows = $imageDb->update(array(
		'status' => 'fetching',
		'modified' => Yadda_Db::now()
	), array(
		'id = ?' => $image->id
	));
	
	// we need to process this image
	if ($rows > 0) {
		$logger->log('Fetching image #'.$image->id, Zend_Log::INFO);
		$logger->log('Source: '.$image->source, Zend_log::DEBUG);
		try {
			Yadda_Model_Image::fetch($image);
			
			// mark the image as 'active'
			$image->getTable()->update(array(
				'status' => 'active',
				'modified' => Yadda_Db::now()
			), array(
				'id = ?' => $image->id
			));
		} catch (Zend_Exception $e) {
			$logger->log('Exception: '.(string) $e, Zend_Log::ERR);
			
			// reset the image to 'new' so that it can be fetched next time
			$image->getTable()->update(array(
				'status' => 'new',
				'modified' => Yadda_Db::now()
			), array(
				'id = ?' => $image->id
			));
		}
		
		sleep(rand(0, 10));
	}
}