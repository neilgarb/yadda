<?php

interface Yadda_Feed_Engine_Interface {
	
	/**
	 * Processed the given feed and returns an array of Yadda_Feed_Stub objects.
	 * 
	 * @param Zend_Db_Table_Row $feed
	 * @return array
	 */
	public function import($feed);
}