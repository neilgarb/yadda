<?php

class Yadda_UserAgent {
	/**
	 * Is the current user agent a mobile device?
	 * 
	 * @return bool
	 */
	public static function isMobile() {
		// is it definitly a mobile device?
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			if (preg_match('#iphone|ipod|blackberry|android|palm|windows\s+ce#i', $_SERVER['HTTP_USER_AGENT'])) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Returns the IP address of the user agent.
	 * 
	 * @return string
	 * @todo Handle transparent proxy (use HTTP_X_FORWARDED_FOR)
	 */
	public static function getIp() {
		return $_SERVER['REMOTE_ADDR'];
	}
}