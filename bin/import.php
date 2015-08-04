<?php

include_once '../bootstrap.php';

ini_set('memory_limit', '1024M');
set_time_limit(0);

// create logger
$logger = Yadda_Log::getInstance();

// get all feeds that have last_fetch = null or last_fetch more than 30m ago
$feedDb = Yadda_Db_Table::getInstance('feed');
$select = $feedDb
	->select()
	->setIntegrityCheck(false)
	->from('feed')
	->joinLeft('site', 'feed.site_id = site.id', array())
	->joinLeft('region', 'feed.region_id = region.id', array(
		'region_lat' => 'lat',
		'region_long' => 'long'
	))
//	->where('site.status = ?', 'active')
//	->where('region.status = ?', 'active')
    ->where('feed.engine = ?', 'vuvuplaza')
	//->where('feed.last_fetch IS NULL OR last_fetch <= ?', date('Y-m-d H:i:s', strtotime('-30 minute')))
	->order('feed.created');
$feeds = $feedDb->fetchAll($select);

foreach ($feeds as $feed) {
	$logger->log('Processing feed for region "'.$feed->region_id.'" from site "'.$feed->site_id.'"', Zend_Log::DEBUG);
	$logger->log('URL: '.$feed->url, Zend_Log::DEBUG);
	$logger->log('Last fetch: '.$feed->last_fetch, Zend_Log::DEBUG);
	try {
		Yadda_Model_Feed::import($feed);
		
		// mark the feed as fetched
		$feedDb->update(array(
			'last_fetch' => Yadda_Db::now(),
			'modified' => Yadda_Db::now()
		), array(
			'id = ?' => $feed->id
		));
	} catch (Exception $e) {
		$logger->log('Exception: '.(string) $e, Zend_Log::ERR);
	}
	
	sleep(rand(0, 10));
}