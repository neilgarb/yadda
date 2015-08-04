<?php

class Yadda_Model_Region {
	public static $allowedSearchParams = array(
		'page' => null,
		'count' => null
	);
	
	/**
	 * Find a region by ID.
	 * 
	 * @param int $id
	 * @throws Yadda_Model_Exception
	 * @return array
	 */
	public static function find($id) {
		$regionDb = Yadda_Db_Table::getInstance('region');
		$select = $regionDb
			->select()
			->from('region')
			->where('status = ?', 'active')
			->where('id = ?', $id);
		$region = $regionDb->fetchRow($select);
		if ($region === null) {
			throw new Yadda_Model_Exception('There is no region with the supplied ID.');
		}
		return self::toArray($region);
	}
	
	/**
	 * Returns a paginated list of regions matching the given parameters.
	 * 
	 * @param array $params
	 * @return array
	 */
	public static function search(array $params) {
		// build query
		$regionDb = Yadda_Db_Table::getInstance('region');
		$select = $regionDb
			->select()
			->from('region')
			->where('status = ?', 'active')
			->order('name');
		
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
		
		$regions = new Zend_Db_Table_Rowset(array(
			'table' => $regionDb,
			'data' => (array) $paginator->getCurrentItems()
		));
		
		foreach ($regions as $region) {
			$return['results'][] = self::toArray($region);
		}
		
		return $return;
	}
	
	/**
	 * Returns an array of all regions.
	 * 
	 * @param array $params
	 * @return array
	 */
	public static function all(array $params = array()) {
		// build query
		$regionDb = Yadda_Db_Table::getInstance('region');
		$select = $regionDb
			->select()
			->from('region')
			->where('status = ?', 'active')
			->order('name');
		
		// fetch
		$regions = $regionDb->fetchAll($select);
		$return = array();
		foreach ($regions as $region) {
			$return[] = self::toArray($region);
		}
		return $return;
	}
	
	/**
	 * Returns an associative array (ID => name) of all regions.
	 * 
	 * @param bool $addEmpty Whether or not to add an empty entry for use in
	 *                       an HTML select box.
	 * @return array
	 */
	public static function index($addEmpty = false) {
		$regionDb = Yadda_Db_Table::getInstance('region');
		$regions = $regionDb->fetchAll(null, 'name');
		$return = (bool) $addEmpty === true ? array('' => '') : array();
		foreach ($regions as $region) {
			$return[$region->id] = $region->name;
		}
		return $return;
	}
	
	/**
	 * Creates a new region.
	 * 
	 * @param array $values
	 * @return string The ID of the new region
	 * @throws Yadda_Model_Exception
	 */
	public static function create(array $values) {
		$insert = array();
		
		// validate - id
		if (!isset($values['id']) || empty($values['id'])) {
			throw new Yadda_Model_Exception('Please provide an ID for the new region.');
		} else {
			$id = trim(strtolower($values['id']));
			$region = null;
			try {
				$region = self::find($id);
			} catch (Yadda_Model_Exception $e) { }
			if ($region !== null) {
				throw new Yadda_Model_Exception('There is already a region with the provided ID.');
			}
			$insert['id'] = $id;
		}
		
		// validate - name
		if (!isset($values['name']) || empty($values['name'])) {
			throw new Yadda_Model_Exception('Please provide a name for the new region.');
		} else {
			$name = trim($values['name']);
			$insert['name'] = $name;
		}
		
		// validate - lat
		if (isset($values['lat'])) {
			$lat = (float) $values['lat'];
			if ($lat <= -180 || $lat >= 180) {
				throw new Yadda_Model_Exception('Please provide a valid latitude.');
			}
			$insert['lat'] = $lat;
		}
		
		// validate - long
		if (isset($values['long'])) {
			$long = (float) $values['long'];
			if ($long <= -180 || $long >= 180) {
				throw new Yadda_Model_Exception('Please provide a valid longitude.');
			}
			$insert['long'] = $long;
		}
		
		// insert
		$regionDb = Yadda_Db_Table::getInstance('region');
		$regionDb->insert(array_merge($insert, array(
			'country_code' => 'za', // for now
			'created' => Yadda_Db::now()
		)));
		
		return $id;
	}
	
	/**
	 * Updates a region.
	 * 
	 * @param int $id
	 * @param array $values
	 * @return void
	 * @throws Yadda_Model_Exception
	 */
	public static function update($id, array $values) {
		$region = self::find($id);
		
		$update = array();
		
		// validate - name
		if (isset($values['name'])) {
			if (empty($values['name'])) {
				throw new Yadda_Model_Exception('Please provide a name for the region.');
			}
			$update['name'] = trim($values['name']);
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
		
		if (sizeof($update) == 0) {
			return; // nothing to do
		}
		
		// update
		$regionDb = Yadda_Db_Table::getInstance('region');
		$regionDb->update(
			array_merge($update, array('modified' => Yadda_Db::now())),
			array('id = ?' => $region['id'])
		);
	}
	
	/**
	 * Deletes a region.
	 * 
	 * @param int $id
	 * @return void
	 */
	public static function delete($id) {
		$region = self::find($id);
		$regionDb = Yadda_Db_Table::getInstance('region');
		$regionDb->update(array(
			'status' => 'deleted',
			'modified' => Yadda_Db::now()
		), array(
			'id = ?' => $region['id']
		));
	}
	
	/**
	 * Converts a region row object to an array.
	 * 
	 * @param Zend_Db_Table_Row $region
	 * @return array
	 */
	public static function toArray($region) {
		return array(
			'id' => $region->id,
			'name' => isset($region->name) ? $region->name : null,
			'lat' => isset($region->lat) && $region->lat !== null ? (float) $region->lat : null,
			'long' => isset($region->long) && $region->long !== null  ? (float) $region->long : null
		);
	}
}