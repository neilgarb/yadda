<?php

include_once '../bootstrap.php';

set_time_limit(0);
$logger = Yadda_Log::getInstance();

// get base mailer URL
$config = Zend_Registry::get('config');
$router = new Www_Controller_Router();
$mailerUrl = 'http://'.$config->domain->www.$router->assemble(array(), 'mailer');
$unsubscribeUrl = 'http://'.$config->domain->www.$router->assemble(array(), 'unsubscribe');

// determine which subscriptions need to be sent
$hour = (int) gmdate('H');
$subscriptionDb = Yadda_Db_Table::getInstance('subscription');
$select = $subscriptionDb
	->select()
	->setIntegrityCheck(false)
	->from('subscription')
	->joinLeft('user', 'subscription.user_id = user.id', array(
		'user_email' => 'email'
	))
	->where('subscription.status = ?', 'active')
	->where('user.status = ?', 'active')
	->where('subscription.hour = ?', $hour)
	->order('id');
$subscriptions = $subscriptionDb->fetchAll($select);

$since = gmdate('Y-m-d\TH:00:00\Z', strtotime('-24 hour'));

foreach ($subscriptions as $subscription) {
	$logger->log('Subscription #'.$subscription->id, Zend_Log::DEBUG);
	
	// run the search
	$params = array(
		'query' => $subscription->query,
		'region' => $subscription->region_id,
		'price' => $subscription->price,
		'since' => $since,
		'count' => 10
	);
	
	try {
		$result = Yadda_Model_Deal::search($params);
	} catch (Yadda_Model_Exception $e) {
		$logger->log('Error while searching: '.$e->getMessage(), Zend_Log::ERR);
		$logger->log('Skipping...', Zend_Log::ERR);
		continue;
	}
	
	$total = $result['total'];
	$logger->log('Found '.$total.' result(s)', Zend_Log::DEBUG);
	if ($total == 0) {
		continue;
	}
	$logger->log('Sending '.$total.' deal(s) to '.$subscription->user_email, Zend_Log::INFO);
	
	// fetch HTML
	try {
		$client = new Zend_Http_Client($mailerUrl, array(
			'timeout' => 10,
			'useragent' => 'send_subscriptions.php'
		));
		foreach ($params as $key => $value) {
			$client->setParameterGet($key, $value);
		}
		$response = $client->request();
	} catch (Exception $e) {
		$logger->log('Error while fetching mailer: '.$e->getMessage(), Zend_Log::ERR);
		$logger->log('Skipping...', Zend_Log::ERR);
		continue;
	}
	
	$body = str_replace(
		'{unsubscribe}',
		$unsubscribeUrl.'?id='.$subscription->id.'&amp;hash='.md5($subscription->id.$config->secret),
		$response->getBody()
	);
	
	// send
	try {
		$mailer = new Zend_Mail('utf-8');
		$mailer->setFrom('no-reply@yadda.co.za', 'yadda.');
		$mailer->setSubject($total.' new deal'.($total == 1 ? '' : 's'));
		$mailer->addTo($subscription->user_email);
		$mailer->setBodyHtml($body);
		$mailer->setBodyText('If you are having difficulty reading this mail, please visit '.$mailerUrl.'?'.http_build_query($params).'.');
		$mailer->send();
	} catch (Exception $e) {
		$logger->log('Error while sending mailer: '.$e->getMessage(), Zend_Log::ERR);
		$logger->log('Skipping...', Zend_Log::ERR);
	}
}
