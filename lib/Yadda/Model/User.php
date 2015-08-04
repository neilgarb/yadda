<?php

class Yadda_Model_User {
	/**
	 * Returns the ID of the user with the provided email address. If the user
	 * account doesn't exist, it is created. If the user account exists but is
	 * inactive, an exception is thrown.
	 * 
	 * @param string $email
	 * @param string|null $name If this is a new user, $name will be used as the
	 *                          user's name.
	 * @return array
	 * @throws Yadda_Model_Exception
	 */
	public static function findByEmail($email, $name = null) {
		$userDb = Yadda_Db_Table::getInstance('user');
		$user = $userDb->fetchRow(array('email = ?' => $email));
		if ($user !== null) {
			if ($user->status != 'active') {
				throw new Yadda_Model_Exception('Your account is no longer active.');
			}
			
		} else {
			$userId = $userDb->insert(array(
				'email' => $email,
				'name' => $name,
				'created' => Yadda_Db::now()
			));
			$user = $userDb->fetchRow(array('email = ?' => $email));
		}
		return self::toArray($user);
	}
	
	/**
	 * Convert a user row object into an array.
	 * 
	 * @param Zend_Db_Table_Row $user
	 * @return array
	 */
	public static function toArray($user) {
		return array(
			'id' => (int) $user->id,
			'email' => isset($user->email) ? $user->email : null,
			'name' => isset($user->name) ? $user->name : null
		);
	}
}