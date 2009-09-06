<?php

interface tx_saltedpasswords_salts {


	/**
	 * Returns length of required salt.
	 * 
	 * @access  public
	 * @return  integer  length of required salt 
	 */
	public function getSaltLength();
	
	/**
	 * Method determines if a given string is a valid salt
	 * 
	 * @param  string  $salt  string to check
	 * @return boolean        true if it's valid salt, otherwise false
	 */
	public function isValidSalt($salt);

	/**
	 * Method creates a salted hash for a given plaintext password
	 * 
	 * @access  public
	 * @param   string  $plaintextPassword  password to create a salted hash from
	 * @param   string  $salt  optional custom salt to use
	 * @return  string  salted hashed password
	 */
	public function getHashedPassword($password, $salt = null);

	/**
	 * Method checks if a given plaintext password is correct by comparing it with
	 * a given salted hashed password.
	 * 
	 * @access  public
	 * @param   string   $plainPW: 
	 * @param   string   $saltedHashPW 
	 * @return  boolean  true, if plaintext password is correct, otherwise false
	 */
	public function checkPassword($plainPW, $saltedHashPW);
}
?>