<?php

class Yadda_Model_Site {
	public static $allowedSearchParams = array(
		'page' => null,
		'count' => null
	);
	
	/**
	 * Finds a site by ID.
	 * 
	 * @param int $id
	 * @throws Yadda_Model_Exception
	 * @return array
	 */
	public static function find($id) {
		$siteDb = Yadda_Db_Table::getInstance('site');
		$select = $siteDb
			->select()
			->setIntegrityCheck(false)
			->from('site')
			->joinLeft('deal', 'site.id = deal.site_id', array('latest_deal' => 'MAX(deal.created)'))
			->where('site.status = ?', 'active')
			->where('site.id = ?', $id)
			->group('site.id');
		$site = $siteDb->fetchRow($select);
		if ($site === null) {
			throw new Yadda_Model_Exception('There is no site with the supplied ID.');
		}
		return self::toArray($site);
	}
	
	/**
	 * Returns a paginated list of all sites matching the given parameters.
	 * 
	 * @param array $params
	 * @return array
	 */
	public static function search(array $params) {
		$siteDb = Yadda_Db_Table::getInstance('site');
		$select = $siteDb
			->select()
			->setIntegrityCheck(false)
			->from('site')
			->joinLeft('deal', 'site.id = deal.site_id', array('latest_deal' => 'MAX(deal.created)'))
			->where('site.status = ?', 'active')
			->group('site.id')
			->order('site.name');

		// fetch
		
		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setCurrentPageNumber(isset($params['page']) ? (int) $params['page'] : 1);
		$paginator->setItemCountPerPage(isset($params['count']) ? (int) $params['count'] : 10);
		
		$return = array(
			'params' => $params,
			'total' => $paginator->getTotalItemCount(),
			'page' => $paginator->getCurrentPageNumber(),
			'pages' => $paginator->count(),
			'results' => array()
		);
		
		$sites = new Zend_Db_Table_Rowset(array(
			'table' => $siteDb,
			'data' => (array) $paginator->getCurrentItems()
		));
		
		foreach ($sites as $site) {
			$return['results'][] = self::toArray($site);
		}
		
		return $return;
	}
	
	/**
	 * Returns an array of all sites.
	 * 
	 * @return array
	 */
	public static function all() {
		$siteDb = Yadda_Db_Table::getInstance('site');
		$select = $siteDb
			->select()
			->setIntegrityCheck(false)
			->from('site')
			->joinLeft('deal', 'site.id = deal.site_id', array('latest_deal' => 'MAX(deal.created)'))
			->group('site.id')
			->order('name');
		$sites = $siteDb->fetchAll($select);
		$return = array();
		foreach ($sites as $site) {
			$return[] = self::toArray($site);
		}
		return $return;
	}
	
	/**
	 * Returns an associative array (ID => name) of all sites.
	 * 
	 * @param bool $addEmpty Whether or not to add an empty entry for use in
	 *                       an HTML select box
	 * @return array
	 */
	public static function index($addEmpty = false) {
		$siteDb = Yadda_Db_Table::getInstance('site');
		$sites = $siteDb->fetchAll(null, 'name');
		$return = (bool) $addEmpty === true ? array('' => '') : array();
		foreach ($sites as $site) {
			$return[$site->id] = $site->name;
		}
		return $return;
	}
	
	/**
	 * Creates a new site.
	 * 
	 * @param array $values
	 * @throws Yadda_Model_Exception
	 * @return int The new site's ID
	 */
	public static function create(array $values) {
		$insert = array();
		
		// validate - id
		if (!isset($values['id']) || empty($values['id'])) {
			throw new Yadda_Model_Exception('Please supply an ID for the new site.');
		} else {
			$id = trim($values['id']);
			$site = null;
			try {
				$site = self::find($id);
			} catch (Yadda_Model_Exception $e) { }
			if ($site !== null) {
				throw new Yadda_Model_Exception('There is already a site with the supplied ID.');
			}
			$insert['id'] = $id;
		}
		
		// validate - name
		if (!isset($values['name']) || empty($values['name'])) {
			throw new Yadda_Model_Exception('Please supply a name for the new site.');
		} else {
			$insert['name'] = trim($values['name']);
		}
		
		// validate - url
		if (!isset($values['url']) || empty($values['url'])) {
			throw new Yadda_Model_Exception('Please provide a URL for this site.');
		} else {
			$url = trim($values['url']);
			try {
				$uri = Zend_Uri::factory($url);
			} catch (Zend_Uri_Exception $e) {
				throw new Yadda_Model_Exception('Please provide a valid URL for the new site.');
			}
			$insert['url'] = $url;
		}
		
		// insert
		$siteDb = Yadda_Db_Table::getInstance('site');
		$siteDb->getAdapter()->beginTransaction();
		try {
			$siteId = $siteDb->insert($insert + array(
				'created' => Yadda_Db::now()
			));
			$siteDb->getAdapter()->commit();
		} catch (Exception $e) {
			$siteDb->getAdapter()->rollBack();
		}
		
		return $siteId;
	}
	
	/**
	 * Updates a site.
	 * 
	 * @param int $id
	 * @param array $values
	 * @throws Yadda_Model_Exception
	 * @return void
	 */
	public static function update($id, array $values) {
		$site = self::find($id);
		
		$update = array();
		$where = array('id = ?' => $site['id']);
		
		// validate - name
		if (isset($values['name'])) {
			if (empty($values['name'])) {
				throw new Yadda_Model_Exception('Please provide a name for the site.');
			} else {
				$update['name'] = trim($values['name']);
			}
		}
		
		// validate - url
		if (isset($values['url'])) {
			if (empty($values['url'])) {
				throw new Yadda_Model_Exception('Please provide a URL for this site.');
			} else {
				$url = trim($values['url']);
				try {
					$uri = Zend_Uri::factory($url);
				} catch (Zend_Uri_Exception $e) {
					throw new Yadda_Model_Exception('Please provide a valid URL for this site.');
				}
				$update['url'] = $url;
			}
		}
		
		// check if there's anything to do?
		if (sizeof($update) == 0) {
			return;
		}
		
		// do the update
		$siteDb = Yadda_Db_Table::getInstance('site');
		$siteDb->getAdapter()->beginTransaction();
		try {
			$siteDb->update($update + array('modified' => Yadda_Db::now()), $where);
			$siteDb->getAdapter()->commit();
		} catch (Exception $e) {
			$siteDb->getAdapter()->rollBack();
			throw new Yadda_Model_Exception('Database error: '.$e->getMessage());
		}
	}
	
	/**
	 * Deletes the specified site.
	 * 
	 * @param string $id
	 * @return void
	 */
	public static function delete($id) {
		$site = self::find($id);
		$siteDb = Yadda_Db_Table::getInstance('site');
		$siteDb->update(array(
			'status' => 'deleted',
			'modified' => Yadda_Db::now()
		), array(
			'id = ?' => $site['id']
		));
	}
	
	/**
	 * Converts a site object to an array.
	 * 
	 * @param Zend_Db_Table_Row $site
	 * @return array
	 */
	public static function toArray($site) {
		return array(
			'id' => $site->id,
			'name' => isset($site->name) ? $site->name : null,
			'latestDeal' => isset($site->latest_deal) ? $site->latest_deal : null,
			'url' => isset($site->url) ? $site->url : null
		);
	}
}