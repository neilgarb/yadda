<?php

class Yadda_Model_Deal {
	public static $priceRanges = array(
		1 => array(0, 99),
		2 => array(100, 499),
		3 => array(500, 1999),
		4 => array(2000)
	);
	
	public static $allowedSearchParams = array(
		'query' => null,
		'region' => null,
		'price' => null,
		'page' => null,
		'count' => null,
		'since' => null
	);
	
	/**
	 * Find a deal by ID.
	 * 
	 * @param int $id
	 * @throws Yadda_Model_Exception
	 * @return array
	 */
	public static function find($id) {
		$dealDb = Yadda_Db_Table::getInstance('deal');
		$select = $dealDb
			->select()
			->setIntegrityCheck(false)
			->from('deal')
			->joinLeft('site', 'deal.site_id = site.id', array(
				'site_name' => 'name',
				'site_url' => 'url'
			))
			->joinLeft('region', 'deal.region_id = region.id', array(
				'region_name' => 'name'
			))
			->where('deal.id = ?', $id)
			->where('deal.status != ?', 'deleted')
			->group('deal.id');
		$deal = $dealDb->fetchRow($select);
		if ($deal === null) {
			throw new Yadda_Model_Exception('There is no deal with the provided ID.');
		}
		return self::toArray($deal);
	}
	
	/**
	 * Returns a paginated list of deals matching the given parameters.
	 *
	 * @param array $params
	 * @return array
	 */
	public static function search(array $params) {
		$return = array(
			'description' => 'All deals',
			'total' => 0,
			'results' => array()
		);
		
		$solrParams = array(
			'q' => '*:*',
			'fq' => array(),
			'wt' => 'json',
			'sort' => 'display_date desc',
			'omitHeader' => 'true'
		);
		
		// query
		if (isset($params['query']) && !empty($params['query'])) {
			$query = trim($params['query']);
			$return['description'] .= ' matching "'.$query.'"';
			$solrParams['q'] = '_text:'.$query;
		}
		
		// region
		if (isset($params['region'])) {
			$region = Yadda_Model_Region::find($params['region']);
			$return['description'] .= ' in '.$region['name'];
			$solrParams['fq'][] = 'region_id:'.$region['id'];
		}
		// price
		if (isset($params['price'])) {
			if (!isset(self::$priceRanges[$params['price']])) {
				throw new Yadda_Model_Exception('Invalid price range "'.$params['price'].'"');
			}
			$range = self::$priceRanges[$params['price']];
			$return['description'] .= ' priced R'.$range[0].' -';
			if (sizeof($range) > 1) {
				$return['description'] .= ' R'.$range[1];
			}
			$solrParams['fq'][] = 'price:['.$range[0].' TO '.(sizeof($range) > 1 ? $range[1] : '*').']';
		}
		
		// since
		if (isset($params['since'])) {
			$solrParams['fq'][] = 'display_date:['.$params['since'].' TO *]';
		}
		
		// count
		$count = 10;
		if (isset($params['count'])) {
			$count = (int) $params['count'];
		}
		$params['count'] = $count;
		$solrParams['rows'] = $count;
		
		// page
		$page = 1;
		if (isset($params['page'])) {
			$page = (int) $params['page'];
		}
		$params['page'] = $page;
		$solrParams['start'] = ($page - 1) * $count;
		
		$return['params'] = $params;
		
		$solr = Yadda_Solr::getInstance();
		$body = $solr->get('/select', $solrParams);
		$data = Zend_Json::decode($body);
		
		$return['total'] = (int) $data['response']['numFound'];
		$return['pages'] = (int) ceil($return['total'] / $count);
		$start = (int) $data['response']['start'];
		$return['page'] = (int) floor($start / $count) + 1;
		
		foreach ($data['response']['docs'] as $doc) {
			$return['results'][] = self::toArray((object) $doc);
		}
		
		return $return;
		
		/*$description = 'All deals';
		
		$dealDb = Yadda_Db_Table::getInstance('deal');
		$select = $dealDb
			->select()
			->setIntegrityCheck(false)
			->from('deal')
			->joinLeft('site', 'deal.site_id = site.id', array(
				'site_name' => 'name'
			))
			->joinLeft('region', 'deal.region_id = region.id', array(
				'region_name' => 'name',
				'region_country_code' => 'country_code'
			))
			->where('deal.status != ?', 'deleted')
			->order(array('deal.display_date DESC', 'deal.id DESC'))
			->group('deal.id');
			
		// check parameters
		
		if (isset($params['region'])) {
			$region = Yadda_Model_Region::find($params['region']);
			$description .= ' in '.$region['name'];
			//$select->where('region.id = ?', $region['id']);
		}
		
		if (isset($params['price'])) {
			foreach (self::$priceRanges as $key => $range) {
				if ($params['price'] == $key) {
					$description .= ' priced ';
					$description .= 'R'.$range[0].' -';
					if (sizeof($range) > 1) {
						$description .= ' R'.$range[1];
					}
					$select->where('price IS NOT NULL');
					$select->where('price >= ?', $range[0]);
					if (sizeof($range) > 1) {
						$select->where('price <= ?', $range[1]);
					}
				}
			}
		}
		
		// fetch
		
		/*$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setCurrentPageNumber(isset($params['page']) ? (int) $params['page'] : 1);
		$paginator->setItemCountPerPage(isset($params['count']) ? (int) $params['count'] : 10);
		
		$return = array(
			'description' => $description,
			'params' => $params,
			'total' => $paginator->getTotalItemCount(),
			'page' => $paginator->getCurrentPageNumber(),
			'pages' => $paginator->count(),
			'results' => array()
		);
		
		$deals = new Zend_Db_Table_Rowset(array(
			'table' => $dealDb,
			'data' => (array) $paginator->getCurrentItems()
		));
		
		foreach ($deals as $deal) {
			$return['results'][] = self::toArray($deal);
		}
		
		return $return;*/
		
	}
	
	public static function allByMapBounds($bounds) {
		if ($bounds === null || !is_array($bounds) || sizeof($bounds) != 4) {
			throw new Yadda_Model_Exception('Invalid bounds.');
		}
		
		$dealDb = Yadda_Db_Table::getInstance('deal');
		$select = $dealDb
			->select()
			->setIntegrityCheck(false)
			->from('deal')
			->joinLeft('site', 'deal.site_id = site.id', array())
			->joinLeft('region', 'deal.region_id = region.id', array())
			->where('deal.status = ?', 'active')
			->where('site.status = ?', 'active')
			->where('region.status = ?', 'active')
			->where('deal.lat IS NOT NULL AND deal.long IS NOT NULL')
			->where('deal.lat <= ?', (float) $bounds[0])
			->where('deal.long <= ?', (float) $bounds[1])
			->where('deal.lat >= ?', (float) $bounds[2])
			->where('deal.long >= ?', (float) $bounds[3])
			->where('deal.display_date >= ?', date('Y-m-d H:i:s', strtotime('-7 day')))
			->order(array('deal.display_date DESC', 'deal.id DESC'));
		$deals = $dealDb->fetchAll($select);
		$return = array();
		foreach ($deals as $deal) {
			$return[] = self::toArray($deal);
		}
		return $return;
	}
	
	/**
	 * Updates a deal.
	 * 
	 * @param int $id
	 * @param array $values
	 * @throws Yadda_Model_Exception
	 * @return void
	 */
	public static function update($id, array $values) {
		$deal = self::find($id);
		
		$update = array();
		$where = array('id = ?' => $deal['id']);
		
		// validate - title
		if (isset($values['title'])) {
			if (empty($values['title'])) {
				throw new Yadda_Model_Exception('Please provide a title for the deal.');
			} else {
				$update['title'] = trim($values['title']);
			}
		}
		
		// validate - description
		if (isset($values['description'])) {
			$update['description'] = empty($values['description']) ? null : trim($values['description']);
		}
		
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
		
		// validate - price
		if (isset($values['price'])) {
			if (empty($values['price'])) {
				$update['price'] = null;
			} else {
				$update['price'] = (float) $values['price'];
			}
		}
		
		// validate - value
		if (isset($values['value'])) {
			if (empty($values['value'])) {
				$update['value'] = null;
			} else {
				$update['value'] = (float) $values['value'];
			}
		}
		
		// validate - discount
		if (isset($values['discount'])) {
			if (empty($values['discount'])) {
				$update['discount'] = null;
			} else {
				$update['discount'] = (float) $values['discount'];
			}
		}
		
		// validate - lat
		if (isset($values['lat'])) {
			if (preg_match('/^\s*$/', $values['lat'])) {
				$update['lat'] = null;
			} else {
				$lat = (float) $values['lat'];
				if ($lat <= -180 || $lat >= 180) {
					throw new Yadda_Model_Exception('Please provide a valid latitude.');
				}
				$update['lat'] = $lat;
			}
		}
		
		// validate - long
		if (isset($values['long'])) {
			if (preg_match('/^\s*$/', $values['long'])) {
				$update['long'] = null;
			} else {
				$long = (float) $values['long'];
				if ($long <= -180 || $long >= 180) {
					throw new Yadda_Model_Exception('Please provide a valid longitude.');
				}
				$update['long'] = $long;
			}
		}
		
		// check if there's anything to do?
		if (sizeof($update) == 0) {
			return;
		}
		
		// do the update
		$dealDb = Yadda_Db_Table::getInstance('deal');
		$dealDb->getAdapter()->beginTransaction();
		try {
			$update['modified'] = Yadda_Db::now();
			$dealDb->update($update, $where);
			$dealDb->getAdapter()->commit();
		} catch (Exception $e) {
			$dealDb->getAdapter()->rollBack();
			throw $e;
		}
	}
	
	/**
	 * Registers a user's interest in this deal.
	 * 
	 * @param int $id
	 * @param string $ip
	 * @return void
	 * @throws Yadda_Model_Exception
	 */
	public static function vote($id, $ip) {
		$deal = self::find($id);
		
		// validate - ip
		if ($ip === null || empty($ip)) {
			throw new Yadda_Model_Exception('No IP address provided.');
		}
		
		$voteDb = Yadda_Db_Table::getInstance('vote');
		
		// is there already a vote with this IP?
		$vote = $voteDb->fetchRow(array(
			'deal_id = ?' => $deal['id'],
			'ip = ?' => $ip
		));
		if ($vote !== null) {
			throw new Yadda_Model_Exception('We\'ve already received a request from you (or at least someone we think is you) to have this deal repeated. Don\'t worry - we\'ll make sure the request is passed on.');
		}
		
		$voteDb->insert(array(
			'deal_id' => $deal['id'],
			'ip' => $ip,
			'created' => time()
		));
	}
	
	/**
	 * Deletes a deal.
	 * 
	 * @param int $id
	 * @return void
	 */
	public static function delete($id) {
		$deal = self::find($id);
		$dealDb = Yadda_Db_Table::getInstance('deal');
		$dealDb->update(array(
			'status' => 'deleted',
			'modified' => Yadda_Db::now()
		), array(
			'id = ?' => $deal['id']
		));
	}
	
	/**
	 * Adds a deal to the featured deals roll.
	 * 
	 * @param int $id
	 * @return void
	 */
	public static function feature($id) {
		$deal = self::find($id);
		$db = Yadda_Db::getInstance();
		$db->insert('feature', array(
			'deal_id' => $deal['id'],
			'created' => time()
		));
	}
	
	/**
	 * Retrieves the $count latest featured deals.
	 * 
	 * @param int $count
	 * @return array
	 */
	public static function featured($count) {
		$count = max(1, (int) $count);
		$dealDb = Yadda_Db_Table::getInstance('deal');
		$select = $dealDb
			->select()
			->setIntegrityCheck(false)
			->from('deal')
			->joinLeft('region', 'deal.region_id = region.id', array(
				'region_name' => 'name'
			))
			->joinLeft('site', 'deal.site_id = site.id', array(
				'site_name' => 'name'
			))
			->joinLeft('feature', 'deal.id = feature.deal_id', array())
			->where('deal.status = ?', 'active')
			->where('region.status = ?', 'active')
			->where('site.status = ?', 'active')
			->where('feature.deal_id IS NOT NULL')
			->order('feature.created DESC')
			->limit($count);
		$deals = $dealDb->fetchAll($select);
		$return = array();
		foreach ($deals as $deal) {
			$return[] = self::toArray($deal);
		}
		return $return;
	}
	
	/**
	 * Converts a feed object to an array.
	 * 
	 * @param Zend_Db_Table_Row $deal
	 * @return array
	 */
	public static function toArray($deal) {
		$config = Zend_Registry::get('config');
		
		return array(
			'id' => (int) $deal->id,
			'guid' => $deal->guid,
			'link' => $deal->link,
			'site' => array(
				'id' => isset($deal->site_id) ? $deal->site_id : null,
				'name' => isset($deal->site_name) ? $deal->site_name : null,
				'url' => isset($deal->site_url) ? $deal->site_url : null
			),
			'region' => array(
				'id' => isset($deal->region_id) ? $deal->region_id : null,
				'name' => isset($deal->region_name) ? $deal->region_name : null,
				'country' => array(
					'code' => isset($deal->region_country_code) ? $deal->region_country_code : null
				)
			),
			'lat' => isset($deal->lat) && $deal->lat !== null ? (float) $deal->lat : null,
			'long' => isset($deal->long) && $deal->long !== null ? (float) $deal->long : null,
			'title' => $deal->title,
			'description' => isset($deal->description) ? $deal->description : null,
			'url' => $deal->link,
			'price' => isset($deal->price) && $deal->price !== null ? (float) $deal->price : null,
			'value' => isset($deal->value) && $deal->value !== null ? (float) $deal->value : null,
			'discount' => isset($deal->discount) && $deal->discount !== null ? (float) $deal->discount : null,
			'date' => strtotime($deal->display_date),
			'expired' => strtotime($deal->display_date) < strtotime('-7 day'), // TODO refine
			'status' => isset($deal->status) ? $deal->status : null
		);
	}
	
	/**
	 * Determines a place's geo coordinates based on its name and a reference
	 * location, using Google's Places API.
	 * 
	 * If no results are returned from the API, or if the place name is
	 * ambiguous (> 1 result), then null is returned.
	 * 
	 * Otherwise, a tuple of (lat, long) is returned.
	 * 
	 * @param array $refGeo
	 * @param string $name
	 * @return array|null
	 */
	public static function getGeoFromPlaceName(array $refGeo, $name) {
		$radius = 100; // in km
		try {
			$client = new Zend_Http_Client('https://maps.googleapis.com/maps/api/place/search/json', array(
				'timeout' => 30
			));
			$client->setParameterGet('location', implode(',', $refGeo));
			$client->setParameterGet('radius', $radius * 1000);
			$client->setParameterGet('name', $name);
			$client->setParameterGet('key', 'AIzaSyBTCF7otQBXFitp2JJ3dk3Thuxyf_d8gNM');
			$client->setParameterGet('sensor','false');
			$response = $client->request();
			$data = Zend_Json::decode($response->getBody());
		} catch (Exception $e) {
			return null;
		}
		if ($data['status'] != 'OK') {
			return null;
		}
		$results = $data['results'];
		if (sizeof($results) != 1) {
			return null;
		}
		$result = $results[0];
		$resultGeo = array(
			$result['geometry']['location']['lat'],
			$result['geometry']['location']['lng']
		);
		
		// test distance between result and reference point - if it's greater
		// than $radius then reject
		$earth = 6371;
		$dLat = deg2rad($refGeo[0] - $resultGeo[0]);
		$dLong = deg2rad($refGeo[1] - $resultGeo[1]);
		$lat1 = deg2rad($refGeo[0]);
		$lat2 = deg2rad($resultGeo[0]);
		$a = pow(sin($dLat / 2), 2) + pow(sin($dLong / 2), 2) * cos($lat1) * cos($lat2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$distance = $earth * $c;
		if ($distance > $radius) {
			return null;
		}
		
		return $resultGeo;
	}
}