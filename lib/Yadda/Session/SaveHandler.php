<?php

class Yadda_Session_SaveHandler implements Zend_Session_SaveHandler_Interface {
	protected $_cache = null;
	
	public function __construct($cache) {
		$this->_cache = $cache;
	}
	
	public function close() {
		return true;
	}
	
	public function destroy($id) {
		if ($this->_cache === null) {
			return true;
		}
		return $this->_cache->remove('session_'.$id);
	}
	
	public function gc($maxlifetime) {
		return true;
	}
	
	public function open($save_path, $name) {
		return true;
	}
	
	public function read($id) {
		if ($this->_cache === null) {
			return '';
		}
		return ($data = $this->_cache->load('session_'.$id)) === false ? '' : $data;
	}
	
	public function write($id, $data) {
		if ($this->_cache === null) {
			return true;
		}
		return $this->_cache->save($data, 'session_'.$id, array(), null);
	}
}