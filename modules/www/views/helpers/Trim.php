<?php

class Www_View_Helper_Trim extends Zend_View_Helper_Abstract {
	public function trim($string, $length) {
		$startLength = strlen($string);
		$suffix = $startLength > $length ? '...' : '';
		$cutLength = $length - strlen($suffix);
		return substr($string, 0, $cutLength).$suffix;
	}
}