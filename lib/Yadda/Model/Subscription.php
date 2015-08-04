<?php

class Yadda_Model_Subscription {
	public static $allowedSearchParams = array(
		'page' => null,
		'count' => null
	);
	
	public static function search($params) {
		// build query
		$subscriptionDb = Yadda_Db_Table::getInstance('subscription');
		$select = $subscriptionDb
			->select()
			->setIntegrityCheck(false)
			->from('subscription')
			->joinLeft('user', 'subscription.user_id = user.id', array(
				'user_email' => 'email'
			))
			->where('subscription.status = ?', 'active')
			->order('created DESC');
			
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
		
		$subscriptions = new Zend_Db_Table_Rowset(array(
			'table' => $subscriptionDb,
			'data' => (array) $paginator->getCurrentItems()
		));
		
		foreach ($subscriptions as $subscription) {
			$return['results'][] = self::toArray($subscription);
		}
		
		return $return;
	}
	
	public static function subscribe(array $params) {
		// validate - email
		if (!isset($params['email']) || empty($params['email'])) {
			throw new Yadda_Model_Exception('Please provide your email address.');
		} else {
			$email = trim($params['email']);
			$validator = new Zend_Validate_EmailAddress();
			if (!$validator->isValid($email)) {
				throw new Yadda_Model_Exception('Please provide a valid email address.');
			}
		}
		
		// validate - time
		if (!isset($params['time']) || empty($params['time'])) {
			throw new Yadda_Model_Exception('Please select when you would like to receive your notifications.');
		} else {
			$time = (int) $params['time'];
			if ($time < 0 || $time > 23) {
				throw new Yadda_Model_Exception('Please select a valid hour GMT.');
			}
		}
		
		// validate - name
		if (!isset($params['name']) || empty($params['name'])) {
			throw new Yadda_Model_Exception('Please provide your name.');
		} else {
			$name = trim($params['name']);
		}
		
		$user = Yadda_Model_User::findByEmail($email, $name);
		
		// create the subscription
		$subscriptionDb = Yadda_Db_Table::getInstance('subscription');
		$subscriptionDb->getAdapter()->beginTransaction();
		try {
			
			$subscriptionDb->insert(array(
				'user_id' => $user['id'],
				'hour' => $time,
				'query' => isset($params['query']) && !empty($params['query']) ? $params['query'] : null,
				'region_id' => isset($params['region']) ? $params['region'] : null,
				'price' => isset($params['price']) ? $params['price'] : null,
				'created' => Yadda_Db::now()
			));
			$subscriptionDb->getAdapter()->commit();
		} catch (Exception $e) {
			$subscriptionDb->getAdapter()->rollBack();
			throw new Yadda_Model_Exception('An error occurred while setting up your subscription. Please try again.');
		}
	}
	
	public static function unsubscribe($id, $hash) {
		// validate - id
		if (empty($id)) {
			throw new Yadda_Model_Exception('Please provide a subscription ID.');
		}
		
		$id = (int) $id;
		$subscriptionDb = Yadda_Db_Table::getInstance('subscription');
		$subscription = $subscriptionDb->fetchRow(array(
			'id = ?' => $id,
			'status = ?' => 'active'
		));
		
		if ($subscription === null) {
			throw new Yadda_Model_Exception('There is no subscription with the provided ID.');
		}
		
		// validate - hash
		if (empty($hash)) {
			throw new Yadda_Model_Exception('Please provide an authentication hash.');
		}
		
		$config = Zend_Registry::get('config');
		if ($hash != md5($id.$config->secret)) {
			throw new Yadda_Model_Exception('Please provide a valid authentication hash.');
		}
		
		// ok all good -- unsub
		$subscriptionDb->update(
			array('status' => 'deleted', 'modified' => Yadda_Db::now()),
			array('id = ?' => $id)
		);
	}
	
	public static function toArray($subscription) {
		$params = array();
		if (isset($subscription->query) && !empty($subscription->query)) {
			$params['query'] = $subscription->query;
		}
		if (isset($subscription->region_id) && !empty($subscription->region_id)) {
			$params['region'] = $subscription->region_id;
		}
		if (isset($subscription->price) && !empty($subscription->price)) {
			$params['price'] = $subscription->price;
		}
		return array(
			'id' => (int) $subscription->id,
			'user' => array(
				'id' => isset($subscription->user_id) ? (int) $subscription->user_id : null,
				'email' => isset($subscription->user_email) ? $subscription->user_email : null
			),
			'params' => $params,
			'created' => isset($subscription->created) ? strtotime($subscription->created) : null
		);
	}
}