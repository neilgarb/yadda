<?php

class Www_View_Helper_Format extends Zend_View_Helper_Abstract {
	public function format($var, $type = null) {
		if ($type === null) {
			if (is_string($var)) {
				$var = trim($var);
				if (empty($var)) {
					return '';
				}
				$var = html_entity_decode($var, ENT_QUOTES, 'utf-8');
				$var = $this->view->escape($var);
				$paras = preg_split('#[\r?\n]+#s', $var);
				$parasClean = array();
				foreach ($paras as $key => $value) {
					$value = preg_replace('/^\s+/', '', $value);
					$value = preg_replace('/\s+$/', '', $value);
					if (!preg_match('/\w/', $value)) {
						continue;
					}
					$parasClean[] = trim($value);
				}
				$var = '<p>'.join('</p><p>', $parasClean).'</p>';
				return $var;
			} else {
				return $var;
			}
		} else if ($type == 'price') {
			$var = (float) $var;
			return 
				'<span class="price">R'.
				number_format(floor($var), 0).'.'.
				sprintf('%02d', $var * 100 - floor($var) * 100).
				'</span>';
		} else if ($type == 'date') {
			return date('j M', (int) $var);
		} else if ($type == 'ago') {
			$thresholds = array(
				365 * 24 * 60 * 60 => 'year',
				30 * 24 * 60 * 60 => 'month',
				7 * 24 * 60 * 60 => 'week',
				24 * 60 * 60 => 'day',
				60 * 60 => 'hour',
				60 => 'minute',
				1 => 'second'
			);
			$diff = time() - (int) $var;
			foreach ($thresholds as $seconds => $name) {
				if ($diff > $seconds) {
					$count = round($diff / $seconds);
					return $count.' '.$name.($count == 1 ? '' : 's').' ago';
				}
			}
			return 'now';
		}
		return $var;
	}
}