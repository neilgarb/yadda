<?php

class SubscriptionController extends Admin_Controller_Action {
	public function listAction() {
		$params = array_merge(
			array('count' => 30),
			array_intersect_key($_GET, Yadda_Model_Subscription::$allowedSearchParams)
		);
		$this->view->subscriptions = Yadda_Model_Subscription::search($params);
	}
}