<?php

class Yadda_Model_Report {
	public static function pull($from, $to) {
		$db = Yadda_Db::getInstance();

		$days = array();

		$cur = $from;
		while ($cur <= $to) {
			$days[$cur] = array();

			// get the number of deals for this day
			$select = $db
				->select()
				->from('deal', array('cnt' => 'COUNT(1)'))
				->where('DATE_FORMAT(created, \'%Y-%m-%d\') = ?', $cur);
			$row = $db->fetchRow($select);
			$days[$cur]['deals'] = (int) $row['cnt'];

			// get the number of votes for the day
			$select = $db
				->select()
				->from('vote', array('cnt' => 'COUNT(1)'))
				->where('DATE_FORMAT(FROM_UNIXTIME(created), \'%Y-%m-%d\') = ?', $cur);
			$row = $db->fetchRow($select);
			$days[$cur]['votes'] = (int) $row['cnt'];
			
			// get the number of subscriptions for the day
			$select = $db
				->select()
				->from('subscription', array('cnt' => 'COUNT(1)'))
				->where('DATE_FORMAT(created, \'%Y-%m-%d\') = ?', $cur);
			$row = $db->fetchRow($select);
			$days[$cur]['subscriptions'] = (int) $row['cnt'];

			$cur = date('Y-m-d', strtotime('+1 day', strtotime($cur)));
		}

		return $days;
	}
}