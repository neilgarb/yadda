<?php

class Yadda_Model_Feed {
	public static $engines = array(
		'247deals' => '247deals',
		'24hoursonly' => '24hoursonly',
		'buywithus' => 'BuyWithUs',
		'citymob' => 'CityMob',
		'collectivecow' => 'CollectiveCow',
        'cotd' => 'Catch of the Day',
		'daddysdeals' => 'Daddy\'s Deals',
		'dealify' => 'Dealify',
		'dealson' => 'Deals On',
		'fooddeals' => 'Food Deals',
		'justhenga' => 'Justhenga',
		'mobstir' => 'Mobstir',
		'onedayonly' => 'OneDayOnly',
		'rss' => 'RSS',
		'skoop' => 'Skoop',
		'vuvuplaza' => 'VuvuPlaza',
		'wicount' => 'WiCount',
		'zappon' => 'Zappon'
	);
	
	/**
	 * Finds a feed by ID.
	 * 
	 * @param int $id
	 * @throws Yadda_Model_Exception
	 * @return array
	 */
	public static function find($id) {
		$feedDb = Yadda_Db_Table::getInstance('feed');
		$feed = $feedDb->fetchRow(array('id = ?' => $id));
		if ($feed === null) {
			throw new Yadda_Model_Exception('There is no feed with the supplied ID.');
		}
		return self::toArray($feed);
	}
	
	/**
	 * Finds all feeds matching the search parameters.
	 * 
	 * @param array $params
	 * @return array
	 */
	public static function all(array $params) {
		$feedDb = Yadda_Db_Table::getInstance('feed');
		$select = $feedDb
			->select()
			->setIntegrityCheck(false)
			->from('feed')
			->joinLeft('site', 'feed.site_id = site.id', array(
				'site_name' => 'name'
			))
			->joinLeft('region', 'feed.region_id = region.id', array(
				'region_name' => 'name'
			))
			->order('feed.id DESC');
			
		// check parameters
		
		if (isset($params['site'])) {
			$select->where('site.id = ?', $params['site']);
		}
		
		if (isset($params['region'])) {
			$select->where('region.id = ?', $params['region']);
		}
		
		if (isset($params['engine'])) {
			$select->where('feed.engine = ?', $params['engine']);
		}
		
		// fetch
		
		$feeds = $feedDb->fetchAll($select);
		
		$return = array();
		foreach ($feeds as $feed) {
			$return[] = self::toArray($feed);
		}
		
		return $return;
	}
	
	/**
	 * Creates a new feed.
	 * 
	 * @param array $values
	 * @throws Yadda_Model_Exception
	 * @return int The new feed ID
	 */
	public static function create(array $values) {
		$insert = array();
		
		// check values
		
		if (!isset($values['site']) || empty($values['site'])) {
			throw new Yadda_Model_Exception('Please specify which site this feed belongs to.');
		} else {
			$site = Yadda_Model_Site::find($values['site']);
			$insert['site_id'] = $site['id'];
		}
		
		if (!isset($values['region']) || empty($values['region'])) {
			throw new Yadda_Model_Exception('Please specify which region this feed belongs to.');
		} else {
			$region = Yadda_Model_Region::find($values['region']);
			$insert['region_id'] = $region['id'];
		}
		
		if (!isset($values['engine'])) {
			throw new Yadda_Model_Exception('Please specify which engine this feed should use.');
		} elseif (!isset(self::$engines[$values['engine']])) {
			throw new Yadda_Model_Exception('Please specify a valid engine.');
		} else {
			$insert['engine'] = $values['engine'];
		}
		
		if (!isset($values['url']) || empty($values['url'])) {
			throw new Yadda_Model_Exception('Please specify the feed\'s URL.');
		} else {
			try {
				$uri = Zend_Uri::factory($values['url']);
				$insert['url'] = $values['url'];
			} catch (Zend_Uri_Exception $e) {
				throw new Yadda_Model_Exception('Please specify a valid URL.');
			}
		}
		
		// insert
		
		$feedDb = Yadda_Db_Table::getInstance('feed');
		
		$feedDb->getAdapter()->beginTransaction();
		try {
			$feedId = $feedDb->insert($insert + array(
				'created' => Yadda_Db::now()
			));
			$feedDb->getAdapter()->commit();
		} catch (Exception $e) {
			$feedDb->getAdapter()->rollBack();
		}
		
		return $feedId;
	}
	
	/**
	 * Updates a feed.
	 * 
	 * @param int $id
	 * @param array $values
	 * @throws Yadda_Model_Exception
	 * @return void
	 */
	public static function update($id, array $values) {
		$feed = self::find($id);
		
		$update = array();
		$where = array('id = ?' => $feed['id']);
		
		// validate - site
		if (isset($values['site'])) {
			$site = Yadda_Model_Site::find($values['site']);
			$update['site_id'] = $site['id'];
		}
		
		// validate - region
		if (isset($values['region'])) {
			$region = Yadda_Model_Region::find($values['region']);
			$update['region_id'] = $region['id'];
		}
		
		// validate - engine
		if (isset($values['engine'])) {
			if (!isset(self::$engines[$values['engine']])) {
				throw new Yadda_Model_Exception('Please select a valid engine.');
			}
			$update['engine'] = $values['engine'];
		}
		
		// validate - url
		if (isset($values['url'])) {
			if (empty($values['url'])) {
				throw new Yadda_Model_Exception('Please supply a URL for this feed.');
			} else {
				try {
					$uri = Zend_Uri::factory($values['url']);
					$update['url'] = $values['url'];
				} catch (Zend_Uri_Exception $e) {
					throw new Yadda_Model_Exception('Please supply a valid URL for this feed.');
				}
			}
		}
		
		// check if there's anything to do?
		if (sizeof($update) == 0) {
			return;
		}
		
		// do the update
		$feedDb = Yadda_Db_Table::getInstance('feed');
		$feedDb->getAdapter()->beginTransaction();
		try {
			$feedDb->update($update + array('modified' => Yadda_Db::now()), $where);
			$feedDb->getAdapter()->commit();
		} catch (Exception $e) {
			$feedDb->getAdapter()->rollBack();
			throw new Yadda_Model_Exception('Database error: '.$e->getMessage());
		}
	}
	
	/**
	 * Deletes a feed
	 * 
	 * @param int $id
	 */
	public static function delete($id) {
		$feed = self::find($id);
		$feedDb = Yadda_Db_Table::getInstance('feed');
		$feedDb->delete(array(
			'id = ?' => $feed['id']
		));
	}
	
	/**
	 * Imports the deals from a feed.
	 * 
	 * @param Zend_Db_Table_Row $feed
	 * @throws Yadda_Model_Exception
	 * @throws Yadda_Feed_Exception
	 * @return void
	 */
	public static function import($feed) {
		if (!isset(self::$engines[$feed->engine])) {
			throw new Yadda_Model_Exception('Unknown feed type "'.$feed->engine.'"');
		}
		
		$class = 'Yadda_Feed_Engine_'.ucfirst(strtolower($feed->engine));
		$engine = new $class();
		$stubs = $engine->import($feed);
		
		$logger = Yadda_Log::getInstance();
		$dealDb = Yadda_Db_Table::getInstance('deal');
		foreach ($stubs as $stub) {
			
			ob_start();
			print_r($stub);
			$logger->log('Stub:'."\n".ob_get_clean(), Zend_Log::DEBUG);
			
			// make sure we have the bare minimum
			if ($stub->getGuid() === null || $stub->getTitle() === null) {
				$logger->log('Rejected: Stub doesn\'t have GUID or title.', Zend_Log::DEBUG);
				continue;
			}
			
			// do we already have this guid for this site?
			$deal = $dealDb->fetchRow(array(
				'site_id = ?' => $feed->site_id,
				'guid = ?' => $stub->getGuid()
			));
			if ($deal !== null) {
				continue;
			}
			
			// do we already have an item with this title for this site today?
			$deal = $dealDb->fetchRow(array(
				'site_id = ?' => $feed->site_id,
				'title = ?' => $stub->getTitle(),
				'DATE_FORMAT(display_date, \'%Y%m%d\') = ?' => date('Ymd', strtotime($stub->getDate()))
			));
			if ($deal !== null) {
				continue;
			}
			
			$logger->log('New entry: '.$stub->getGuid(), Zend_Log::INFO);
			
			// determine geo coords
			$geo = $stub->getGeo();
			if ($geo === null && $feed->region_lat !== null && $feed->region_long !== null) {
				
				// get deal title
				$title = $stub->getTitle();
				$title = utf8_decode($title);
				$matches = array();
				preg_match_all('#(at|from)\s+((([A-Z][\w\']+)\s*)+)#', $title, $matches);
				foreach ($matches[2] as $match) {
					$logger->log('Testing "'.$match.'" for coords', Zend_Log::DEBUG);
					$match = trim($match);
					
					// remove prices/stopwords/etc.
					if (preg_match('/^R\d+$/', $match)) {
						continue;
					}
					if ($match == 'On') {
						continue;
					}
					
					// get coordinates
					$test = Yadda_Model_Deal::getGeoFromPlaceName(array(
						(float) $feed->region_lat,
						(float) $feed->region_long
					), $match);
					if ($test !== null) {
						$logger->log('Found geo coords: '.join(' ', $test), Zend_Log::INFO);
						$geo = $test;
						break;
					}
				}
			}
			
			// insert the new deal
			$dealDb->getAdapter()->beginTransaction();
			try {
				$insert = array(
					'site_id' => $feed->site_id,
					'region_id' => $feed->region_id,
					'guid' => $stub->getGuid(),
					'title' => $stub->getTitle(),
					'description' => $stub->getDescription(),
					'link' => $stub->getLink(),
					'display_date' => date('Y-m-d H:i:s', strtotime($stub->getDate())),
					'price' => $stub->getPrice(),
					'value' => $stub->getValue(),
					'discount' => $stub->getDiscount(),
					'lat' => $geo !== null ? $geo[0] : null,
					'long' => $geo !== null ? $geo[1] : null,
					'status' => 'active',
					'created' => Yadda_Db::now()
				);
				
				$dealId = $dealDb->insert($insert);
				
				// check for images
				foreach ($stub->getImages() as $image) {
					$logger->log('New image: '.$image, Zend_Log::INFO);
					$imageId = $dealDb->getAdapter()->insert('image', array(
						'deal_id' => $dealId,
						'source' => $image,
						'created' => Yadda_Db::now()
					));
				}
				
				$dealDb->getAdapter()->commit();
			} catch (Zend_Exception $e) {
				$dealDb->getAdapter()->rollBack();
				throw new Yadda_Feed_Exception('Error importing deal: '.$e->getMessage());
			}
		}
	}
	
	/**
	 * Converts a feed row object to an array.
	 * 
	 * @param Zend_Db_Table_Row $feed
	 * @return array
	 */
	public static function toArray($feed) {
		return array(
			'id' => (int) $feed->id,
			'site' => array(
				'id' => isset($feed->site_id) ? $feed->site_id : null,
				'name' => isset($feed->site_name) ? $feed->site_name : null
			),
			'region' => array(
				'id' => isset($feed->region_id) ? $feed->region_id : null,
				'name' => isset($feed->region_name) ? $feed->region_name : null
			),
			'engine' => isset($feed->engine) ? $feed->engine : null,
			'url' => isset($feed->url) ? $feed->url : null
		);
	}
}